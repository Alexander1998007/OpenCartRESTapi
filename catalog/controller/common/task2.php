<?php
class ControllerCommonTask2 extends Controller {
	public function index() {
		$this->document->setTitle($this->config->get('config_meta_title'));
		$this->document->setDescription($this->config->get('config_meta_description'));
		$this->document->setKeywords($this->config->get('config_meta_keyword'));

		if (isset($this->request->get['route'])) {
			$this->document->addLink($this->config->get('config_url'), 'canonical');
		}

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('common/task2', $data));
	}

    public function getOrganizationsAjax(){
	    $data_from_api = array();
        $organizations_from_api = $this->load->controller('api/pipedrive/getOrganizations');
        $notes_from_api = $this->load->controller('api/pipedrive/getNotes');
        foreach ($organizations_from_api as $organization_from_api){
            if ($organization_from_api->notes_count > 0){
                foreach ($notes_from_api as $note_from_api){
                    if ($note_from_api->organization->name == $organization_from_api->name){
                        $organization_from_api->note_list[] = trim($note_from_api->content);
                    }
                }
            }
            $data_from_api[] = $organization_from_api;
        }
        if (!empty($data_from_api) && is_array($data_from_api)){
            $data['organization_head']['name'] = 'Имя';
            $data['organization_head']['address'] = 'Адрес';
            $data['organization_head']['people_count'] = 'Контакты';
            $data['organization_head']['open_deals_count'] = 'Открытые сделки';
            $data['organization_head']['closed_deals_count'] = 'Закрытые сделки';
            $data['organization_head']['next_activity_date'] = 'Дата следующей задачи';
            $data['organization_head']['owner_name'] = 'Владелец';
            $data['organization_head']['note_list'] = 'Заметки';
            $data['organizations'] = $data_from_api;
            $this->response->setOutput($this->load->view('common/organizations_list', $data));
        }
    }

    public function getPersonsAjax(){
        $data_from_api = array();
        $persons_from_api = $this->load->controller('api/pipedrive/getPersons');
        $notes_from_api = $this->load->controller('api/pipedrive/getNotes');
        foreach ($persons_from_api as $person_from_api){
            if ($person_from_api->notes_count > 0){
                foreach ($notes_from_api as $note_from_api){
                    if ($note_from_api->person->name == $person_from_api->name){
                        $person_from_api->note_list[] = trim($note_from_api->content);
                    }
                }
            }
            $data_from_api[] = $person_from_api;
        }
        if (!empty($data_from_api) && is_array($data_from_api)){
            $data['person_head']['name'] = 'Имя';
            $data['person_head']['org_name'] = 'Организация';
            $data['person_head']['open_deals_count'] = 'Открытые сделки';
            $data['person_head']['closed_deals_count'] = 'Закрытые сделки';
            $data['person_head']['next_activity_date'] = 'Дата следующей задачи';
            $data['person_head']['owner_name'] = 'Владелец';
            $data['person_head']['note_list'] = 'Заметки';
            $data['persons'] = $data_from_api;
            $this->response->setOutput($this->load->view('common/persons_list', $data));
        }
    }

    public function getDealsAjax(){
        $data_from_api = array();
        $deals_from_api = $this->load->controller('api/pipedrive/getDeals');
        $notes_from_api = $this->load->controller('api/pipedrive/getNotes');
        foreach ($deals_from_api as $deal_from_api){
            if ($deal_from_api->notes_count > 0){
                foreach ($notes_from_api as $note_from_api){
                    if ($note_from_api->deal->title == $deal_from_api->title){
                        $deal_from_api->note_list[] = trim($note_from_api->content);
                    }
                }
            }
            $data_from_api[] = $deal_from_api;
        }
        if (!empty($data_from_api) && is_array($data_from_api)){
            $data['deal_head']['title'] = 'Название';
            $data['deal_head']['org_name'] = 'Организация';
            $data['deal_head']['formatted_value'] = 'Стоимость';
            $data['deal_head']['note_list'] = 'Заметки';
            $data['deals'] = $data_from_api;
            $this->response->setOutput($this->load->view('common/deals_list', $data));
        }
    }

    public function getTasksAjax(){
        $data_from_api = $this->load->controller('api/pipedrive/getActivities');
        if (!empty($data_from_api) && is_array($data_from_api)){
            $data['task_head']['subject'] = 'Тема';
            $data['task_head']['person_name'] = 'Контактное лицо';
            $data['task_head']['org_name'] = 'Организация';
            $data['task_head']['due_date_time'] = 'Срок выполнения';
            $data['task_head']['duration'] = 'Продолжительность';
            $data['task_head']['owner_name'] = 'Владелец';
            $data['task_head']['note'] = 'Заметки';
            $data['tasks'] = $data_from_api;
            $this->response->setOutput($this->load->view('common/tasks_list', $data));
        }
    }
}
