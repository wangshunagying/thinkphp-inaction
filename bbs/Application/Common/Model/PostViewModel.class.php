<?php
/**
 * Project: thinkphp-inaction
 * User: xialei
 * Date: 2016/9/27 0027
 * Time: 15:30
 */

namespace Common\Model;


use Common\Lib\Page;
use Think\Exception;
use Think\Model\ViewModel;

class PostViewModel extends ViewModel
{
    public $pk = 'postId';
    public $viewFields = array(
        'Post' => array('postId', 'title', 'viewCount', 'replyCount', 'createdAt', 'updatedAt', 'content', 'boardId', 'userId'),
        'User' => array('nickname' => 'userNickname', 'avatar' => 'userAvatar', 'createdAt' => 'userCreatedAt', 'createdIp' => 'userCreatedIp', 'score' => 'userScore', 'postCount' => 'userPostCount', '_on' => 'Post.userId=User.userId'),
        'Board' => array('name' => 'boardName', 'icon' => 'boardIcon', '_on' => 'Post.boardId=Board.boardId')
    );

    /**
     * 帖子列表
     * @param int $userId
     * @param int $boardId
     * @param int $size
     * @return array
     */
    public function getList($userId = 0, $boardId = 0, $size = 10)
    {
        $condition = array();
        if ($userId > 0) {
            $condition['userId'] = $userId;
        }
        if ($boardId > 0) {
            $condition['boardId'] = $boardId;
        }

        $count = $this->where($condition)->count();
        $page = new Page($count, $size);
        $list = $this->where($condition)->order('postId DESC')->limit($page->firstRow . ',' . $page->listRows)->select();

        return [$list, $page->show(), $count];
    }

    /**
     * 最新的帖子
     * @param $count
     * @return mixed
     */
    public function latest($count)
    {
        return $this->order('postId DESC')->limit($count)->select();
    }

    /**
     * 查看帖子
     * @param $id
     * @param bool $addViews
     * @return mixed
     * @throws Exception
     */
    public function view($id, $addViews = true)
    {

        if ($addViews) {
            $user = session('user');
            if (empty($user)) {
                $key = get_client_ip(1);
            } else {
                $key = $user['userId'];
            }
            $this->addViews($id, $key);
        }
        $data = $this->find($id);
        if (empty($data)) {
            throw new Exception('帖子不存在');
        }
        return $data;
    }

    /**
     * 添加已读数
     * @param $id
     * @param $param
     */
    private function addViews($id, $param)
    {
        $cacheKey = $id . $param;
        $cache = S($cacheKey);
        if (!$cache) {
            $this->where(array('postId' => $id))->setInc('viewCount');
            S($cacheKey, 1, 3600);
        }
    }
}