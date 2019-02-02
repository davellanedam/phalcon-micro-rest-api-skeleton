<?php

class CitiesController extends ControllerBase
{
    /**
     * Private functions
     */
    private function checksIfCityAlreadyExists($name)
    {
        $name = trim($name);
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
        }
    }

    private function checksIfCityToUpdateAlreadyExists($name, $id)
    {
        $name = trim($name);
        $conditions = 'name = :name: AND id != :id:';
        $parameters = array(
            'name' => $name,
            'id' => $id,
        );
        $city = Cities::findFirst(
            array(
                $conditions,
                'bind' => $parameters,
            )
        );
        if ($city) {
            $this->buildErrorResponse(409, 'common.THERE_IS_ALREADY_A_RECORD_WITH_THAT_NAME');
        }
    }

    private function createCity($name, $country)
    {
        $city = new Cities();
        $city->name = trim($name);
        $city->country = trim($country);
        $this->tryToSaveData($city, 'common.COULD_NOT_BE_CREATED');
        return $city;
    }

    private function updateCity($city, $name, $country)
    {
        $city->name = trim($name);
        $city->country = trim($country);
        $this->tryToSaveData($city, 'common.COULD_NOT_BE_UPDATED');
        return $city;
    }

    /**
     * Public functions
     */
    public function index()
    {
        $this->initializeGet();
        $options = $this->buildOptions('name asc', $this->request->get('sort'), $this->request->get('order'), $this->request->get('limit'), $this->request->get('offset'));
        $filters = $this->buildFilters($this->request->get('filter'));
        $cities = $this->findElements('Cities', $filters['conditions'], $filters['parameters'], 'id, name, country', $options['order_by'], $options['offset'], $options['limit']);
        $total = $this->calculateTotalElements('Cities', $filters['conditions'], $filters['parameters']);
        $data = $this->buildListingObject($cities, $options['rows'], $total);
        $this->buildSuccessResponse(200, 'common.SUCCESSFUL_REQUEST', $data);
    }

    public function create()
    {
        $this->initializePost();
        $this->checkForEmptyData([$this->request->getPost('name'), $this->request->getPost('country')]);
        $this->checksIfCityAlreadyExists($this->request->getPost('name'));
        $city = $this->createCity($this->request->getPost('name'), $this->request->getPost('country'));
        $this->registerLog();
        $this->buildSuccessResponse(201, 'common.CREATED_SUCCESSFULLY', $city->toArray());
    }

    public function get($id)
    {
        $this->initializeGet();
        $city = $this->findElementById('Cities', $id);
        $this->buildSuccessResponse(200, 'common.SUCCESSFUL_REQUEST', $city->toArray());
    }

    public function update($id)
    {
        $this->initializePatch();
        $this->checkForEmptyData([$this->request->getPut('name'), $this->request->getPut('country')]);
        $this->checksIfCityToUpdateAlreadyExists($this->request->getPut('name'), $id);
        $city = $this->updateCity($this->findElementById('Cities', $id), $this->request->getPut('name'), $this->request->getPut('country'));
        $this->registerLog();
        $this->buildSuccessResponse(200, 'common.UPDATED_SUCCESSFULLY', $city->toArray());
    }

    public function delete($id)
    {
        $this->initializeDelete();
        if ($this->tryToDeleteData($this->findElementById('Cities', $id))) {
            $this->registerLog();
            $this->buildSuccessResponse(200, 'common.DELETED_SUCCESSFULLY');
        }
    }
}
