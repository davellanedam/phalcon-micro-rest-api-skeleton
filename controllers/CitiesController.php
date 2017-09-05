<?php

class CitiesController extends ControllerBase
{
    /**
     * Gets cities.
     */
    public function index()
    {
        // Verifies if is get request
        $this->initializeGet();

        // Init
        $rows = 5;
        $order_by = 'name asc';
        $offset = 0;
        $limit = $offset + $rows;

        // Handles Sort querystring (order_by)
        if ( $this->request->get('sort') != null && $this->request->get('order') != null ) {
            $order_by = $this->request->get('sort') . " " . $this->request->get('order');
        }

        // Gets rows_per_page
        if ( $this->request->get('limit') != null ) {
            $rows = $this->getQueryLimit($this->request->get('limit'));
            $limit = $rows;
        }

        // Calculate the offset and limit
        if ( $this->request->get('offset') != null ) {
            $offset = $this->request->get('offset');
            $limit = $rows;
        }

        // Init arrays
        $conditions = [];
        $parameters = [];

        // Filters simple (no left joins needed)
        if ( $this->request->get('filter') != null ) {
            $filter = json_decode($this->request->get('filter'), true);
            foreach($filter as $key => $value) {
                array_push($conditions, $key . " LIKE :" . $key . ":");
                $parameters = $this->array_push_assoc($parameters, $key, "%".trim($value)."%");
            }
            $conditions = implode(' AND ', $conditions);
        }

        // Search DB
        $city = Cities::find(
            array(
                $conditions,
                'bind' => $parameters,
                'columns' => 'id, name, country',
                'order' => $order_by,
                'offset' => $offset,
                'limit' => $limit
            )
        );

        // Gets total
        $total = Cities::count(
            array(
                $conditions,
                'bind' => $parameters
            )
        );

        if (!$city) {
            $this->buildErrorResponse(404, 'common.NO_RECORDS');
        } else {
            $data = [];
            $data = $this->array_push_assoc($data, 'rows_per_page', $rows);
            $data = $this->array_push_assoc($data, 'total_rows', $total);
            $data = $this->array_push_assoc($data, 'rows', $city->toArray());
            $this->buildSuccessResponse(200, 'common.SUCCESSFUL_REQUEST', $data);
        }
    }

    /**
     * Creates a new city.
     */
    public function create()
    {
        // Verifies if is post request
        $this->initializePost();

        // Start a transaction
        $this->db->begin();

        if (empty($this->request->getPost('name')) || empty($this->request->getPost('country'))) {
            $this->buildErrorResponse(400, 'common.INCOMPLETE_DATA_RECEIVED');
        } else {
            $name = trim($this->request->getPost('name'));
            // checks if city already exists
            $conditions = 'name = :name:';
            $parameters = array(
                'name' => $name,
            );
            $city = Cities::findFirst(
                array(
                    $conditions,
                    'bind' => $parameters,
                )
            );
            if ($city) {
                $this->buildErrorResponse(409, 'common.THERE_IS_ALREADY_A_RECORD_WITH_THAT_NAME');
            } else {
                $newCity = new Cities();
                $newCity->name = $name;
                $newCity->country = trim($this->request->getPost('country'));
                if (!$newCity->save()) {
                    $this->db->rollback();
                    // Send errors
                    $errors = array();
                    foreach ($newCity->getMessages() as $message) {
                        $errors[] = $message->getMessage();
                    }
                    $this->buildErrorResponse(400, 'common.COULD_NOT_BE_CREATED', $errors);
                } else {
                    // Commit the transaction
                    $this->db->commit();
                    // Register log in another DB
                    $this->registerLog();

                    $data = $newCity->toArray();
                    $this->buildSuccessResponse(201, 'common.CREATED_SUCCESSFULLY', $data);
                }
            }
        }
    }

    /**
     * Gets city based on unique key.
     */
    public function get($id)
    {
        // Verifies if is get request
        $this->initializeGet();

        $conditions = 'id = :id:';
        $parameters = array(
            'id' => $id,
        );
        $city = Cities::findFirst(
            array(
                $conditions,
                'bind' => $parameters,
                'columns' => 'id, name, country',
            )
        );
        if (!$city) {
            $this->buildErrorResponse(404, 'common.NOT_FOUND');
        } else {
            $data = $city->toArray();
            $this->buildSuccessResponse(200, 'common.SUCCESSFUL_REQUEST', $data);
        }
    }

    /**
     * Updates city based on unique key.
     */
    public function update($id)
    {
        // Verifies if is patch request
        $this->initializePatch();

        // Start a transaction
        $this->db->begin();

        $conditions = 'id = :id:';
        $parameters = array(
            'id' => $id,
        );
        $city = Cities::findFirst(
            array(
                $conditions,
                'bind' => $parameters,
            )
        );
        if (!$city) {
            $this->buildErrorResponse(404, 'common.NOT_FOUND');
        } else {
            $name = trim($this->request->getPut('name'));
            // checks if city already exists
            $conditions = 'name = :name: AND id != :id:';
            $parameters = array(
                'name' => $name,
                'id' => $id,
            );
            $cityCheck = Cities::findFirst(
                array(
                    $conditions,
                    'bind' => $parameters,
                )
            );
            if ($cityCheck) {
                $this->buildErrorResponse(409, 'common.THERE_IS_ALREADY_A_RECORD_WITH_THAT_NAME');
            } else {
                if (empty($this->request->getPut('name')) || empty($this->request->getPut('country'))) {
                    $this->buildErrorResponse(400, 'common.INCOMPLETE_DATA_RECEIVED');
                } else {
                    $city->name = $name;
                    $city->country = trim($this->request->getPut('country'));
                    if (!$city->save()) {
                        $this->db->rollback();
                        // Send errors
                        $errors = array();
                        foreach ($city->getMessages() as $message) {
                            $errors[] = $message->getMessage();
                        }
                        $this->buildErrorResponse(400, 'common.COULD_NOT_BE_UPDATED', $errors);
                    } else {
                        // Commit the transaction
                        $this->db->commit();
                        // Register log in another DB
                        $this->registerLog();

                        $data = $city->toArray();
                        $this->buildSuccessResponse(200, 'common.UPDATED_SUCCESSFULLY', $data);
                    }
                }
            }
        }
    }

    /**
     * Deletes city based on unique key.
     */
    public function delete($id)
    {
        // Verifies if is get request
        $this->initializeDelete();

        // Start a transaction
        $this->db->begin();

        $conditions = 'id = :id:';
        $parameters = array(
            'id' => $id,
        );
        $city = Cities::findFirst(
            array(
                $conditions,
                'bind' => $parameters,
            )
        );
        if (!$city) {
            $this->buildErrorResponse(404, 'common.NOT_FOUND');
        } else {
            if (!$city->delete()) {
                $this->db->rollback();
                // Send errors
                $errors = array();
                foreach ($city->getMessages() as $message) {
                    $errors[] = $message->getMessage();
                }
                $this->buildErrorResponse(400, 'common.COULD_NOT_BE_DELETED', $errors);
            } else {
                // Commit the transaction
                $this->db->commit();
                // Register log in another DB
                $this->registerLog();

                $this->buildSuccessResponse(200, 'common.DELETED_SUCCESSFULLY');
            }

        }
    }
}
