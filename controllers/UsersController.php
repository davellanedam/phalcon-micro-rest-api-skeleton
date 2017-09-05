<?php

class UsersController extends ControllerBase
{
    /**
     * Gets all users
     */
    public function index()
    {
        // Verifies if is get request
        $this->initializeGet();

        // Gets limit for query
        $limit = $this->getQueryLimit($this->request->get("limit"));

        // Init
        $rows = 5;
        $order_by = 'firstname asc, lastname asc';
        $offset = 0;
        $limit = $offset + $rows;

        // Handles Sort querystring (order_by)
        if ($this->request->get('sort') != null && $this->request->get('order') != null) {
            $order_by = $this->request->get('sort').' '.$this->request->get('order');
        }

        // Gets rows_per_page
        if ($this->request->get('limit') != null) {
            $rows = $this->getQueryLimit($this->request->get('limit'));
            $limit = $rows;
        }

        // Calculate the offset and limit
        if ($this->request->get('offset') != null) {
            $offset = $this->request->get('offset');
            $limit = $rows;
        }

        // Init arrays
        $conditions = [];
        $parameters = [];

        if ( $this->request->get('filter') == null ) {
            $conditions = implode(' AND ', $conditions);
        }

        // Filters for select with left joins
        if ( $this->request->get('filter') != null ) {
            $filter = json_decode($this->request->get('filter'), true);
            foreach($filter as $key => $value) {

                $tmp_conditions = [];
                // special case filtering LEFT JOINS
                switch ($key) {
                    case 'id':
                        $tmp_filter = 'Users.id';
                        break;
                    case 'username':
                        $tmp_filter = 'Users.username';
                        break;
                    case 'firstname':
                        $tmp_filter = 'Users.firstname';
                        break;
                    case 'lastname':
                        $tmp_filter = 'Users.lastname';
                        break;
                    case 'birthday':
                        $tmp_filter = 'Users.birthday';
                        break;
                    case 'email':
                        $tmp_filter = 'Users.email';
                        break;
                    case 'phone':
                        $tmp_filter = 'Users.phone';
                        break;
                    case 'mobile':
                        $tmp_filter = 'Users.mobile';
                        break;
                    case 'level':
                        $tmp_filter = 'Users.level';
                        break;
                    case 'city':
                        $tmp_filter = 'Users.city';
                        break;
                    case 'country':
                        $tmp_filter = 'Users.country';
                        break;
                    case 'authorised':
                        $tmp_filter = 'Users.authorised';
                        break;
                    case 'lastAccess_date':
                        $tmp_filter = 'ua.date';
                        break;
                    case 'lastAccess_ip':
                        $tmp_filter = 'ua.ip';
                        break;
                    case 'lastAccess_domain':
                        $tmp_filter = 'ua.domain';
                        break;
                    case 'lastAccess_browser':
                        $tmp_filter = 'ua.browser';
                        break;
                    default:
                        $tmp_filter = $key;
                        break;
                }

                $tmp_filter = explode(' OR ', $tmp_filter);

                foreach($tmp_filter as $filter_value) {
                    array_push($tmp_conditions, $filter_value . " LIKE :" . str_replace(".", "_", $key) . ":");
                    $parameters = $this->array_push_assoc($parameters, str_replace(".", "_", $key), "%".trim($value)."%");
                }

                $tmp_conditions = implode(' OR ', $tmp_conditions);
                array_push($conditions, "(" . $tmp_conditions . ")");

            }
            // puts all conditions together
            $conditions = implode(' AND ', $conditions);
        }

        if ($conditions == null) {
            $conditions = '';
        }

        // Search DB
        $users = Users::query()
        ->columns(['Users.id', 'Users.username', 'Users.firstname', 'Users.lastname', 'Users.birthday', 'Users.email', 'Users.phone', 'Users.mobile', 'Users.level', 'Users.city', 'Users.country', 'Users.authorised', 'ua.date AS lastAccess_date', 'ua.ip AS lastAccess_ip', 'ua.domain AS lastAccess_domain', 'ua.browser AS lastAccess_browser'])
            ->leftJoin('UsersAccess', 'Users.username = ua.username', 'ua')
            ->where($conditions)
            ->bind($parameters)
            ->groupBy('Users.id')
            ->orderBy($order_by)
            ->limit($limit, $offset)
            ->execute();

        // Gets total
        $tmp_total = Users::query()
            ->columns(['Users.id'])
            ->leftJoin('UsersAccess', 'Users.username = ua.username', 'ua')
            ->where($conditions)
            ->bind($parameters)
            ->groupBy('Users.id')
            ->execute();

        $total = count($tmp_total);

        if (!$users) {
            $this->buildErrorResponse(404, 'common.NO_RECORDS');
        } else {
            $data = $users->toArray();
            $user_data = array();
            $out_data = array();
            foreach ($data as $key => $value) {
                if (empty($value['lastAccess_date'])) {
                    $value = $this->array_push_assoc($value, 'last_access', "");
                } else {
                    $this_user_last_access = array(
                        'date' => $this->utc_to_iso8601($value['lastAccess_date']),
                        'ip' => $value['lastAccess_ip'],
                        'domain' => $value['lastAccess_domain'],
                        'browser' => $value['lastAccess_browser']
                    );
                    $value = $this->array_push_assoc($value, 'last_access', $this_user_last_access);
                }
                unset($value['lastAccess_date']);
                unset($value['lastAccess_ip']);
                unset($value['lastAccess_domain']);
                unset($value['lastAccess_browser']);

                array_push($user_data, $value);
            }

            $out_data = $this->array_push_assoc($out_data, 'rows_per_page', $rows);
            $out_data = $this->array_push_assoc($out_data, 'total_rows', $total);
            $out_data = $this->array_push_assoc($out_data, 'rows', $user_data);
            $this->buildSuccessResponse(200, 'common.SUCCESSFUL_REQUEST', $out_data);
        }
    }

    /**
     * Creates a new user
     */
    public function create()
    {
        // Verifies if is post request
        $this->initializePost();

        // Start a transaction
        $this->db->begin();

        if ( empty($this->request->getPost("username")) || empty($this->request->getPost("firstname")) || empty($this->request->getPost("newPassword"))  || empty($this->request->getPost("email")) ) {
            $this->buildErrorResponse(400, "common.INCOMPLETE_DATA_RECEIVED");
        } else {
            $username = trim($this->request->getPost("username"));
            if ($username == 'admin') {
                $this->buildErrorResponse(409, "common.COULD_NOT_BE_CREATED");
            }
            // checks if user already exists
            $conditions = "username = :username:";
            $parameters = array(
                "username" => $username
            );
            $user = Users::findFirst(
                array(
                    $conditions,
                    "bind" => $parameters
                )
            );
            if ($user) {
                $this->buildErrorResponse(409, "profile.ANOTHER_USER_ALREADY_REGISTERED_WITH_THIS_USERNAME");
            } else {
                $newUser = new Users();
                $newUser->email = trim($this->request->getPost("email"));
                $newUser->username = $username;
                $newUser->firstname = trim($this->request->getPost("firstname"));
                $newUser->lastname = trim($this->request->getPost("lastname"));
                $newUser->level = trim($this->request->getPost("level"));
                $newUser->phone = trim($this->request->getPost("phone"));
                $newUser->mobile = trim($this->request->getPost("mobile"));
                $newUser->address = trim($this->request->getPost("address"));
                $newUser->city = trim($this->request->getPost("city"));
                $newUser->country = trim($this->request->getPost("country"));
                $newUser->birthday = trim($this->request->getPost("birthday"));
                if (!$this->request->getPost("authorised") || $this->request->getPost("authorised") == 0) {
                    $newUser->authorised = 0;
                } else if ($this->request->getPost("authorised") == 1) {
                    $newUser->authorised = 1;
                }

                // Encrypts temporary password
                $password_hashed = password_hash($this->request->getPost("newPassword"), PASSWORD_BCRYPT);
                $newUser->password = $password_hashed;

                if (!$newUser->save()) {
                    $this->db->rollback();
                    // Send errors
                    $errors = array();
                    foreach ($newUser->getMessages() as $message) {
                        $errors[] = $message->getMessage();
                    }
                    $this->buildErrorResponse(400, "common.COULD_NOT_BE_CREATED", $errors);
                } else {
                    // Commit the transaction
                    $this->db->commit();

                    // Register log in another DB
                    $this->registerLog();

                    $data = $newUser->toArray();
                    // removes DB autoincrement id from response
                    unset($data['password']);
                    unset($data['block_expires']);
                    unset($data['login_attempts']);
                    $this->buildSuccessResponse(201, 'common.CREATED_SUCCESSFULLY', $data);
                }
            }

        }
    }

    /**
     * Gets user based on unique key
     */
    public function get($id)
    {
        // Verifies if is get request
        $this->initializeGet();

        $conditions = "id = :id:";
        $parameters = array(
            "id" => $id
        );
        $user = Users::findFirst(
            array(
                $conditions,
                "bind" => $parameters,
                'columns' => 'id, username, firstname, lastname, birthday, email, phone, mobile, address, level, city, country, authorised',
            )
        );
        if (!$user) {
            $this->buildErrorResponse(404, "common.NOT_FOUND");
        } else {
            $data = $user->toArray();
            // finds if user has last access.
            $conditions = "username = :username:";
            $parameters = array(
                "username" => $user->username
            );
            $last_access = UsersAccess::find(
                array(
                    $conditions,
                    "bind" => $parameters,
                    'columns' => 'date, ip, domain, browser',
                    'order' => 'id DESC',
                    'limit' => 10
                )
            );
            if ($last_access) {
                $array = array();
                // Gets user last access
                $user_last_access = $last_access->toArray();
                foreach ($user_last_access as $key_last_access => $value_last_access) {
                    $this_user_last_access = array(
                        'date' => $this->utc_to_iso8601($value_last_access['date']),
                        'ip' => $value_last_access['ip'],
                        'domain' => $value_last_access['domain'],
                        'browser' => $value_last_access['browser']
                    );
                    $array[] = $this_user_last_access;
                }
                if (empty($array)) {
                    $data = $this->array_push_assoc($data, 'last_access', "");
                } else {
                    $data = $this->array_push_assoc($data, 'last_access', $array);
                }
            }

            $this->buildSuccessResponse(200, "common.SUCCESSFUL_REQUEST", $data);
        }
    }

    /**
     * Updates user based on unique key
     */
    public function update($id)
    {
        // Verifies if is get request
        $this->initializePatch();

        // Start a transaction
        $this->db->begin();

        $conditions = "id = :id:";
        $parameters = array(
            "id" => $id
        );
        $user = Users::findFirst(
            array(
                $conditions,
                "bind" => $parameters
            )
        );
        if (!$user) {
            $this->buildErrorResponse(404, "common.NOT_FOUND");
        } else {
            if ( empty($this->request->getPut("firstname")) ) {
                $this->buildErrorResponse(400, "common.INCOMPLETE_DATA_RECEIVED");
            } else {
                $user->firstname = trim($this->request->getPut("firstname"));
                $user->lastname = trim($this->request->getPut("lastname"));
                $user->birthday = trim($this->request->getPut("birthday"));
                $user->email = trim($this->request->getPut("email"));
                $user->level = trim($this->request->getPut("level"));
                $user->phone = trim($this->request->getPut("phone"));
                $user->mobile = trim($this->request->getPut("mobile"));
                $user->address = trim($this->request->getPut("address"));
                $user->city = trim($this->request->getPut("city"));
                $user->country = trim($this->request->getPut("country"));
                if (!$this->request->getPut("authorised") || $this->request->getPut("authorised") == 0) {
                    $user->authorised = 0;
                } else if ($this->request->getPut("authorised") == 1) {
                    $user->authorised = 1;
                }
                if (!$user->save()) {
                    $this->db->rollback();
                    // Send errors
                    $errors = array();
                    foreach ($user->getMessages() as $message) {
                        $errors[] = $message->getMessage();
                    }
                    $this->buildErrorResponse(400, "common.COULD_NOT_BE_UPDATED", $errors);
                } else {

                    // Commit the transaction
                    $this->db->commit();

                    // Register log in another DB
                    $this->registerLog();

                    $data = $user->toArray();
                    // removes DB autoincrement id from response
                    unset($data['password']);
                    unset($data['block_expires']);
                    unset($data['login_attempts']);
                    $this->buildSuccessResponse(200, "common.UPDATED_SUCCESSFULLY", $data);
                }
            }
        }
    }

    /**
     * Changes user password
     */
    public function changePassword($id)
    {
        // Verifies if is post request
        $this->initializePatch();

        // Start a transaction
        $this->db->begin();

        if ( empty($this->request->getPut("newPassword")) ) {
            $this->buildErrorResponse(400, "common.INCOMPLETE_DATA_RECEIVED");
        } else {
            $conditions = "id = :id:";
            $parameters = array(
                "id" => $id
            );
            $user = Users::findFirst(
                array(
                    $conditions,
                    "bind" => $parameters
                )
            );
            if (!$user) {
                $this->buildErrorResponse(404, "common.NOT_FOUND");
            } else {
                $password_hashed = password_hash($this->request->getPut("newPassword"), PASSWORD_BCRYPT);
                $user->password = $password_hashed;
                if (!$user->save()) {
                    $this->db->rollback();
                    // Send errors
                    $errors = array();
                    foreach ($user->getMessages() as $message) {
                        $errors[] = $message->getMessage();
                    }
                    $this->buildErrorResponse(400, "common.COULD_NOT_BE_UPDATED", $errors);
                } else {
                    // Commit the transaction
                    $this->db->commit();

                    // Register log in another DB
                    $this->registerLog();

                    $this->buildSuccessResponse(200, "change-password.PASSWORD_SUCCESSFULLY_UPDATED");
                }

            }
        }
    }

}
