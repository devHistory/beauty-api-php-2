<?php


namespace MyApp\Services;


use MongoDB\BSON\ObjectId;

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
     * 填充用户信息 - 昵称签名
     * @param null $data
     * @param null $key
     * @param bool|false $auto
     * @param array $field
     * @return array|null
     */
    public function fillUserInfo($data = null, $key = null, $field = ['name'], $auto = true)
    {
        if ($key) {
            foreach ($data as $value) {
                $uid [] = new ObjectId($value->$key);
            }
        }
        else {
            foreach ($data as $value) {
                $uid [] = new ObjectId($value);
            }
        }

        // 返回字段
        foreach ($field as $v) {
            $fieldReturn[$v] = 1;
        }
        // 查询mongodb
        $db = $this->config->mongodb->db;
        $accounts = $this->mongodb->$db->accounts->find(
            ['_id' => ['$in' => $uid]],
            ['projection' => $fieldReturn]
        );


        // 返回字典格式
        if ($auto == false) {
            $result = [];
            foreach ($accounts as $account) {
                foreach ($field as $f) {
                    $result[$account->_id->__toString()][$f] = isset($account->$f) ? $account->$f : '';
                }
            };
            return $result;
        }

        // 返回自动集成格式
        if (!$key) {
            $result = [];
            foreach ($accounts as $account) {
                $d = [];
                foreach ($field as $f) {
                    $d[$f] = isset($account->$f) ? $account->$f : '';
                }
                $result[] = ['uid' => $account->_id->__toString()] + $d;
            };
            return $result;
        }

        // 返回复杂集成
        $dict = [];
        foreach ($accounts as $account) {
            $d = [];
            foreach ($field as $f) {
                $d[$f] = isset($account->$f) ? $account->$f : '';
            }
            $dict[$account->_id->__toString()] = $d;
        };
        foreach ($data as $k => $v) {
            if (isset($dict[$v->$key])) {
                foreach ($field as $f) {
                    $data[$k][$f] = $dict[$v->$key][$f];
                }
            }
        }
        return $data;
    }

}