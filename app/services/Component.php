<?php


namespace MyApp\Services;


use MongoDB\BSON\ObjectId;
use Phalcon\Config;
use Phalcon\Config\Adapter\Yaml;
use Symfony\Component\Yaml\Yaml as SFYaml;

class Component
{

    public function __construct($di)
    {
        $this->di = $di;
    }


    public function __get($name)
    {
        return $this->di[$name];
    }


    /**
     * 获取规则配置
     * @return mixed
     */
    private function getRules()
    {
        $data = $this->cache->get('_rules');
        if ($data) {
            return json_decode($data, true);
        }
        if (function_exists('yaml_parse_file')) {
            $config = new Yaml(APP_DIR . "/config/rule.yml");
        }
        else {
            $config = new Config(SFYaml::parse(file_get_contents(APP_DIR . "/config/rule.yml")));
        }
        $this->cache->set('_rules', json_encode($config), 86400);
        return $config->toArray();
    }


    /**
     * 积分等级
     * @param string $uid
     * @param int $score
     * @param string $type
     */
    public function score($uid = '', $score = 0, $type = '')
    {
        $newScore = $this->redis->zIncrBy('score', $score, $uid);
        $rule = $this->getRules()['level'];
        krsort($rule);
        $level = 0;
        foreach ($rule as $lv => $lvScore) {
            if ($newScore >= $lvScore) {
                $level = $lv;
                break;
            }
        }

        // update level
        if ($newScore - $score < $rule[$level]) {
            $mongodb = $this->di['mongodb'];
            $db = $this->di['config']->mongodb->db;
            $mongodb->$db->accounts->updateOne(
                ['_id' => new ObjectId($uid)],
                ['$set' => ['level' => $level]]
            );
        }

        // TODO :: logs
    }


    /**
     * 排行榜
     * rankHomeView | rankReward | rankContribute | rankPostLike | rankPostComment | rankPostView
     *
     * 例:
     * 累计贡献 rank(rankContribute, $FromUid, $score);
     * 详细贡献 rank(rankContribute|<toUid>, $FromUid, $score);
     *
     * @param string $key
     * @param string $id
     * @param int $score
     * @return mixed
     */
    public function rank($key = '', $id = '', $score = 1)
    {
        return $this->redis->zIncrBy($key, $score, $id);
        // TODO :: 周排行
    }


    /**
     * 填充用户信息 - 从缓存获取
     * @param array $uid
     * @param array $fields
     * @return array
     */
    public function fillUserFromCache($uid = [], $fields = ['name'])
    {
        if (!$uid) {
            return [];
        }

        $oneLine = false;
        if (is_string($uid)) {
            $oneLine = true;
            $uid = [$uid];
        }

        // Get From Cache
        $cacheKeys = [];
        foreach ($uid as $u) {
            $cacheKeys[] = '_account|' . $u;
        }
        $cacheData = $this->cache->mget($cacheKeys);
        $dataDict = [];
        foreach ($cacheData as $k => $v) {
            if (!$v) {
                $miss[] = $uid[$k];
                continue;
            }
            $dataDict[$uid[$k]] = json_decode($v, true);
        }

        // 查询 MongoDB
        if (!empty($miss)) {
            // 缓存键名
            $projection = [
                'name'    => 1,
                'gender'  => 1,
                'age'     => 1,
                'certify' => 1,
                'level'   => 1,
                'avatar'  => 1,
                'desc'    => 1,
                'account' => 1,
                'uuid'    => 1
            ];

            $uidList = [];
            foreach ($miss as $u) {
                $uidList[] = new ObjectId($u);
            }

            $db = $this->config->mongodb->db;
            $accounts = $this->mongodb->$db->accounts->find(
                ['_id' => ['$in' => $uidList]],
                ['projection' => $projection]
            );
            foreach ($accounts as $account) {
                $oid = $account->_id->__toString();
                $this->cache->set('_account|' . $oid, json_encode($account), 86400 * 14);
                $dataDict[$oid] = $account;
            }
        }

        $result = [];
        foreach ($uid as $u) {
            $d = [];
            foreach ($fields as $f) {
                if (empty($dataDict[$u][$f])) {
                    continue;
                }
                $d[$f] = $dataDict[$u][$f];
            }
            $result[] = ['uid' => $u] + $d;
        }
        if ($oneLine == true) {
            return array_pop($result);
        }
        return $result;
    }


    /**
     * 填充用户信息
     * @param null $data
     * @param null $key
     * @param array $field
     * @return array
     */
    public function fillUserByKey($data = null, $key = null, $field = ['name'])
    {
        foreach ($data as $value) {
            $uid[] = $value[$key];
        }
        if (empty($uid)) {
            return [];
        }
        $accounts = $this->fillUserFromCache($uid, $field);

        $dict = [];
        foreach ($accounts as $u) {
            $uid = $u['uid'];
            $dict[$uid] = $u;
        };

        foreach ($data as $k => $v) {
            if (!isset($dict[$v[$key]])) {
                continue;
            }
            foreach ($field as $f) {
                if (empty($dict[$v[$key]][$f])) {
                    continue;
                }
                $data[$k][$f] = $dict[$v[$key]][$f];
            }
        }
        return $data;
    }


    /**
     * 签名
     * @param array $data
     * @param string $signKey
     * @param string $as
     * @param string $di
     * @return string
     */
    public function createSign($data = array(), $signKey = '', $as = '=', $di = '&')
    {
        ksort($data);
        $string = '';
        foreach ($data as $key => $value) {
            $string .= "$key{$as}$value{$di}";
        }
        return md5(rtrim($string, $di) . $signKey);
    }

}