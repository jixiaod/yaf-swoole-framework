<?php
/**
 * Description DatabaseUserProvider
 * 
 * PHP version 5
 * 
 * @category PHP
 * @package ImReworks\Auth
 * @author Gang Ji <gang.ji@moji.com>
 * @copyright 2014-2016 Moji Fengyun Software Technology Development Co., Ltd.
 * @license license from Moji Fengyun Software Technology Development Co., Ltd.
 * @link http://www.moji.com
 */

namespace ImReworks\Auth;

class DatabaseUserProvider
{

    protected $_conn;

    public function __construct($conn)
    {
        $this->_conn = $conn;
        $this->_conn->setDbname(DB_BACKADMIN);
    }

    public function retriveUserByPassword($username, $password)
    {
        $db = $this->_conn->getConnection('slave');

        $select = $db->select()
            ->from(array('u' => 'admin_user'), 
            array('id', 'gid', 'username','nickname', 
                'truename', 'email', 'sex', 'status',
                'adminer', 'loginip', 'logintime',
                'createtime', 'createuser', 'tel', 'up_password_time'))

            ->joinLeft(array('g' => 'admin_usergroup'), 
                'u.gid = g.id', array('name AS group_name'))

            ->where('u.username = ?', $username)
            ->where('u.password = ?', md5($password))
            ->limit(1);

        $db->closeConnection();

        return $db->fetchRow($select);
    }

    public function retriveAuthesByGid($group_id, $space_id)
    {
        $db = $this->_conn->getConnection('slave');

        $select = $db->select()
            ->from(array('aa' => 'admin_auth'), array('id', 'pid', 'url', 'name', 'order', 'is_nav', 'is_public'))
            ->joinLeft(array('ag' => 'admin_usergroup_auth'), 'ag.auth_id = aa.id', array())
            ->where("`ag`.`id` = '{$group_id}' AND `ag`.`space_id` = '{$space_id}'")
            ->orWhere('aa.is_public = ?', '1')
            ->order('aa.order DESC');

        $db->closeConnection();
        $data = $db->fetchAll($select);

        return unique_multidim_array($data, 'id');
    }

    public function retriveSpacesByGid($group_id)
    {
        $db = $this->_conn->getConnection('slave');

        $select = $db->select()
            ->distinct()
            ->from(array('ua' => 'admin_usergroup_auth'), array('space_id'))
            ->join(array('s' => 'admin_space'), 's.id = ua.space_id', array('id', 'name', 'module', 'title', 'url'))
            ->where('ua.id = ?', $group_id);

        $db->closeConnection();

        return $db->fetchAll($select);
    }

    public function retriveAuthesBySpaceId($space_id)
    {
        $db = $this->_conn->getConnection('slave');

        $select = $db->select()
            ->from('admin_auth', array('id', 'pid', 'url', 'name', 'order', 'is_nav', 'is_public', 'space_id'))
            ->where('space_id = ?', $space_id)
            ->order('order DESC');

        $db->closeConnection();

        return $db->fetchAll($select);
    }

    public function retriveSpaces()
    {
        $db = $this->_conn->getConnection('slave');

        $select = $db->select()
            ->from('admin_space', array('id', 'name', 'module', 'title', 'url'));

        $db->closeConnection();

        return $db->fetchAll($select);
   
    }
    
    public function updateLoginInfo($uid)
    {
        $db = $this->_conn->getConnection('master');

        $rows = array(
            'loginip' => get_client_ip(),
            'logintime' => SYSTEM_TIME,
        );

        $where = $db->quoteInto('id = ?', $uid);

        $rows_affected = $db->update('admin_user', $rows, $where);

        $db->closeConnection();

        return $rows_affected > 0 ? true : false;

    }

    public function createAccount($data)
    {
        // 指定使用主库
        $db = $this->_conn->getConnection('master');

        try {
            $row = array(
                'gid' => 2,
                'username' => $data['username'],
                'nickname' => $data['nickname'],
                'password' => md5($data['password']),
                'truename' => $data['truename'],
                'email' => $data['username'],
                'status' => 1,
                'adminer' => 0,
                'loginip' => '',
                'logintime' => '',
                'createtime' => SYSTEM_TIME,
                'createuser' => '',
                'tel' => $data['tel'],
                'up_password_time' => SYSTEM_TIME
            );  

            $rows_affect = $db->insert('admin_user', $row);

            $return = $rows_affect > 0 ? $db->lastInsertId() : false;
            $db->closeConnection();

            return $return;

        } catch (Exception $e) {
            $db->closeConnection();
            Logger::write($e->getMessage(), ERR);

            return false;
        }
    }

    public function duplicateUsername($username)
    {
        $db = $this->_conn->getConnection('slave');

        $select = $db->select()
            ->from('admin_user', array('id', 'username'))
            ->where('username = ?', $username);

        return !empty($db->fetchAll($select));
    }

    public function updatePassword($uid, $password)
    {
        // 指定使用主库
        $db = $this->_conn->getConnection('master');

        try {
            $row = array(
                'password' => md5($password),
                'up_password_time' => SYSTEM_TIME
            );  

            $where = $db->quoteInto('id = ?', $uid);

            $rows_affected = $db->update('admin_user', $row, $where);
            $db->closeConnection();

            return true;

        } catch (Exception $e) {
            $db->closeConnection();
            Logger::write($e->__tostring(), ERR);

            return false;
        }
    }

    public function retrivePassword($uid) 
    {
        $db = $this->_conn->getConnection('slave');

        $select = $db->select()
            ->from('admin_user', array('password'))
            ->where('id = ?', $uid);

        return $db->fetchOne($select);
    }
}




