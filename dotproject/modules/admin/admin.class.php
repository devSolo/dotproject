<?php /* ADMIN $Id$ */
/**
* User Class
*/
class CUser extends CDpObject {
	var $user_id = NULL;
	var $user_username = NULL;
	var $user_password = NULL;
	var $user_parent = NULL;
	var $user_type = NULL;
	var $user_contact = NULL;
	var $user_signature = NULL;

	function CUser() {
		$this->CDpObject( 'users', 'user_id' );
	}

	function check() {
		if ($this->user_id === NULL) {
			return 'user id is NULL';
		}
		if ($this->user_password !== NULL) {
			$this->user_password = db_escape( trim( $this->user_password ) );
		}
		// TODO MORE
		return NULL; // object is ok
	}

	function store() {
		$msg = $this->check();
		if( $msg ) {
			return get_class( $this )."::store-check failed";
		}
		$q  = new DBQuery;
		if( $this->user_id ) {
		// save the old password
			$perm_func = "updateLogin";
			$q->addTable('users');
			$q->addQuery('user_password');
			$q->addWhere("user_id = $this->user_id");
			$pwd = $q->loadResult();
			if ($pwd != $this->user_password) {
				$this->user_password = md5($this->user_password);
			} else {
				$this->user_password = null;
			}

			$ret = db_updateObject( 'users', $this, 'user_id', false );
		} else {
			$perm_func = "addLogin";
			$this->user_password = md5($this->user_password);
			$ret = db_insertObject( 'users', $this, 'user_id' );
		}
		if( !$ret ) {
			return get_class( $this )."::store failed <br />" . db_error();
		} else {
			$acl =& $GLOBALS['AppUI']->acl();
			$acl->$perm_func($this->user_id, $this->user_username);
			return NULL;
		}
	}

	function delete( $oid = NULL ) {
		$id = $this->user_id;
		$result = parent::delete($oid);
		if (! $result) {
			$acl =& $GLOBALS['AppUI']->acl();
			$acl->deleteLogin($id);
		}
		return $result;
 	}
}
?>