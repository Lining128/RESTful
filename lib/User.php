<?php


require __DIR__.'/errorcode.php';
class User
{
  private $db;
  public function __construct($_db)
  {
    $this->$_db = $_db;
  }

    /**
     * @param $username
     * @param $password
     * @return mixed
     * @throws ErrorException
     */
    public function login($username, $password)
  {
      if (empty($username)){
          throw new ErrorException("用户名不能为空",errorcode::USERNAME_CANNOT_EMPTY);
      }
      if (empty($password)) {
          throw new ErrorException("密码不能为空", errorcode::PASSWORD_CANNOT_EMPTY);
      }
      $sql = 'select * from user where username =:username and  password =:password';
      $password = $this->_md5($password);
      $stmt = $this->_db->prepare($sql);
      $stmt->bindParam(':username',$username);
      $stmt->bindParam(':password',$password);
      $stmt->execute();
      $user = $stmt->fetch(PDO::FETCH_ASSOC);
      if (empty($user)){
          throw new ErrorException("用户名密码错误",errorcode::USERNAME_OR_PASSWORD_INVALID);
      }
      unset($user['password']);
      return $user;
  }

    /**
     * @param $username
     * @param $password
     * @return array
     * @throws ErrorException
     */
    public function register($username, $password)
  {
      if (empty($username)){
          throw new ErrorException("用户名不能为空",errorcode::USERNAME_CANNOT_EMPTY);
      }
      if ($this->_isUsernameExists($username)) {
          throw new Exception("用户名已存在", errorcode::USERNAME_EXISTS);
      }
      if (empty($password)) {
          throw new ErrorException("密码不能为空", errorcode::PASSWORD_CANNOT_EMPTY);
      }
      $sql = 'insert into user (username,password,created_at) value (:username,:password,:created_at)';
      $created_at = time();
      $password = $this->_md5($password);
      $stmt = $this->prepare($sql);
      $stmt->bindParam(':username',$username);
      $stmt->bindParam(':password',$password);
      $stmt->bindParam(':created_at',$created_at);
      if (!$stmt->execute()){
          throw new ErrorException("注册失败",errorcode::REGISTER_FAIL);
      }
      return[
          'userId' => $this->_db->lastInsertId(),
          'username' => $username,
          'created_at' => $created_at

      ];
  }
  private function _md5($string,$key = 'imooc')
  {
      return md5($string . $key);
  }

    /**
     * @param $username
     * @return bool
     */
    private function _isUsernameExists($username)
  {
    $exists = false;
    $sql = 'select * from user where username =:username';
    $stmt = $this->_db->prepare($sql);
    $stmt->bindParam(':username',$username);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return !empty($result);
  }
}
