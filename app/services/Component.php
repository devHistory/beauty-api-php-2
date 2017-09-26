<?php


namespace MyApp\Services;


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

}