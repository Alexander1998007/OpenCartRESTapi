<?php
class ControllerApiPipedrive extends Controller {
    public function getActivities(){
        $api_url = "https://chosen-rake.pipedrive.com/api/v1/activities?api_token=";
        $data_from_api = $this->getDataFromUrl($api_url);
        if (!empty($data_from_api)) {
            return $data_from_api;
        }
    }

    public function getOrganizations(){
        $api_url = "https://chosen-rake.pipedrive.com/api/v1/organizations?api_token=";
        $data_from_api = $this->getDataFromUrl($api_url);
        if (!empty($data_from_api)) {
            return $data_from_api;
        }
    }

    public function getPersons(){
        $api_url = "https://chosen-rake.pipedrive.com/api/v1/persons?api_token=";
        $data_from_api = $this->getDataFromUrl($api_url);
        if (!empty($data_from_api)) {
            return $data_from_api;
        }
    }

    public function getDeals(){
        $api_url = "https://chosen-rake.pipedrive.com/api/v1/deals?api_token=";
        $data_from_api = $this->getDataFromUrl($api_url);
        if (!empty($data_from_api)){
            return $data_from_api;
        }
    }

    public function getNotes(){
        $api_url = "https://chosen-rake.pipedrive.com/api/v1/notes?api_token=";
        $data_from_api = $this->getDataFromUrl($api_url);
        if (!empty($data_from_api)){
            return $data_from_api;
        }
    }

	private function getDataFromUrl(string $api_url = ''){
        if (PIPEDRIVE_API && !empty($api_url)) {
            $url = $api_url.PIPEDRIVE_API;
            $client = curl_init($url);
            curl_setopt($client,CURLOPT_RETURNTRANSFER,true);
            $response = curl_exec($client);
            $result = json_decode($response);
            if (!empty($result->data)){
                return $result->data;
            }else{
                return null;
            }
        }
    }
}
