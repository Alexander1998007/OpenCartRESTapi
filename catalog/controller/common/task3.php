<?php
class ControllerCommonTask3 extends Controller {
	public function index() {
		$this->document->setTitle($this->config->get('config_meta_title'));
		$this->document->setDescription($this->config->get('config_meta_description'));
		$this->document->setKeywords($this->config->get('config_meta_keyword'));

		if (isset($this->request->get['route'])) {
			$this->document->addLink($this->config->get('config_url'), 'canonical');
		}

        $data['action'] = $this->url->link('common/task3/fileChange');

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('common/task3', $data));
	}

	public function fileChange(){
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateImport()) {

            $data['excel'] = array();
            $message = "";
            $targetPath = DIR_UPLOAD . $_FILES['file']['name'];

            if (isset($_POST["import"])) {
                $allowedFileType = [
                    'application/vnd.ms-excel',
                    'text/xls',
                    'text/xlsx',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                ];

                if (in_array($_FILES["file"]["type"], $allowedFileType)) {
                    // save file
                    move_uploaded_file($_FILES['file']['tmp_name'], $targetPath);

                    // read the file
                    try {
                        $inputFileType = PHPExcel_IOFactory::identify($targetPath);
                        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
                        $objPHPExcel = $objReader->load($targetPath);
                    } catch (Exception $e) {
                        die('Error loading file "'.pathinfo($targetPath,PATHINFO_BASENAME).'": '.$e->getMessage());
                    }

                    //  Get worksheet dimensions
                    try {
                        $sheet = $objPHPExcel->getSheet(0);
                    } catch (PHPExcel_Exception $e) {
                        die('Error loading sheet "'.pathinfo($targetPath,PATHINFO_BASENAME).'": '.$e->getMessage());
                    }

                    $highestRow = $sheet->getHighestRow();
                    $highestColumn = $sheet->getHighestColumn();

                    //  Loop through each row of the worksheet in turn
                    for ($row = 1; $row <= $highestRow; $row++) {
                        //  Read a row of data into an array
                        $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
                            NULL,
                            TRUE,
                            FALSE);

                        // Pushed all row to array
                        array_push($data['excel'], $rowData[0]);
                    }
                } else {
                    $type = "error";
                    $message = "Invalid File Type. Upload Excel File.";
                }

                // make changes to the file
                if (!empty($data['excel'])){
                    $changed_data = $this->makeChangesInFile($data['excel']);
                }

                // create and download new modified file
                if (!empty($changed_data) && is_array($changed_data)){
                    try {
                        // Создание документа
                        $xls = new PHPExcel();
                        // Установка сводки документа
                        $xls->getProperties()->setTitle("Data");
                        $xls->getProperties()->setSubject("Data");
                        $xls->getProperties()->setDescription("Modified file according to the task");
                        $date = date('d.m.yy');
                        $xls->getProperties()->setCreated($date);

                        // Создаем новый лист, далее работаем с ним через переменную $sheet.
                        $xls->setActiveSheetIndex(0);
                        $sheet = $xls->getActiveSheet();
                        $sheet->setTitle('Data');

                        // Авто ширина колонок по содержимому
                        $sheet->getColumnDimension("A")->setAutoSize(true);
                        $sheet->getColumnDimension("B")->setAutoSize(true);
                        $sheet->getColumnDimension("C")->setAutoSize(true);
                        $sheet->getColumnDimension("D")->setAutoSize(true);
                        $sheet->getColumnDimension("E")->setAutoSize(true);
                        $sheet->getColumnDimension("F")->setAutoSize(true);
                        $sheet->getColumnDimension("G")->setAutoSize(true);
                        $sheet->getColumnDimension("H")->setAutoSize(true);
                        $sheet->getColumnDimension("I")->setAutoSize(true);

                        // Цикл для заполнения категориями
                        foreach ($changed_data as $key => $value){
                            $cell_in_sheet = $key + 1;
                            $sheet->setCellValue("A". $cell_in_sheet,  $value[0]);
                            $sheet->setCellValue("B". $cell_in_sheet,  $value[1]);
                            $sheet->setCellValue("C". $cell_in_sheet,  $value[2]);
                            $sheet->setCellValue("D". $cell_in_sheet,  $value[3]);
                            $sheet->setCellValue("E". $cell_in_sheet , $value[4]);
                            $sheet->setCellValue("F". $cell_in_sheet,  $value[5]);
                            $sheet->setCellValueExplicit("G". $cell_in_sheet,  $value[6], PHPExcel_Cell_DataType::TYPE_STRING);
                            $sheet->setCellValue("H". $cell_in_sheet,  $value[7]);
                            $sheet->setCellValue("I". $cell_in_sheet,  $value[8]);
                        }

                        // Скачивания excel
                        header("Expires: Mon, 1 Apr 1974 05:00:00 GMT");
                        header("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
                        header("Cache-Control: no-cache, must-revalidate");
                        header("Pragma: no-cache");
                        header("Content-type: application/vnd.ms-excel");
                        header("Content-Disposition: attachment; filename=data.csv");

                        $objWriter = new PHPExcel_Writer_Excel5($xls);
                        $objWriter->save('php://output');

                        exit;

                    } catch (Exception $e) {

                    }
                }
            }
        }
    }

    private function makeChangesInFile($data){
	    // in the phone field, delete all characters except numbers
        $data_clear_numbers = array();
	    foreach ($data as $i => $record){
	        if ($i > 0){
                $record[6] = preg_replace("/[^0-9.]/", "", $record[6]);
            }
            $data_clear_numbers[] = $record;
        }

	    // in the birthday field change the date format from yyyy-mm-dd to dd.mm.yy.
        $data_change_birthday_format = array();
        foreach ($data_clear_numbers as $i => $record){
            if ($i > 0){
                $record[8] = date("m.d.y", strtotime($record[8]));  ;
            }
            $data_change_birthday_format[] = $record;
        }
        return $data_change_birthday_format;
    }

    private function validateImport() {
        if (isset($this->request->files['file']['error']) && $this->request->files['file']['error'] != '0') {
            $this->error['file'] = $this->language->get('error_file');
        }
        return !$this->error;
    }
}
