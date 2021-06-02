<?php
class ControllerCommonTask4 extends Controller {
	public function index() {
		$this->document->setTitle($this->config->get('config_meta_title'));
		$this->document->setDescription($this->config->get('config_meta_description'));
		$this->document->setKeywords($this->config->get('config_meta_keyword'));

		if (isset($this->request->get['route'])) {
			$this->document->addLink($this->config->get('config_url'), 'canonical');
		}

        $data['action'] = $this->url->link('common/task4/fileChange');

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('common/task4', $data));
	}

	public function fileChange(){
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateImport()) {

            $data['xml'] = array();
            $targetPath = DIR_UPLOAD . $_FILES['file']['name'];

            if (isset($_POST["import"])) {
                $allowedFileType = ['text/xml'];

                if (in_array($_FILES["file"]["type"], $allowedFileType)) {
                    // save file
                    move_uploaded_file($_FILES['file']['tmp_name'], $targetPath);

                    // interpreting an XML file into an DOMDocument
                    $data['xml'] = new DOMDocument();
                    if (file_exists($targetPath)) {
                        $data['xml']->load($targetPath);
                    } else {
                        exit('Не удалось открыть файл test.xml.');
                    }

                    // make changes to the file
                    if (!empty($data['xml'])){
                        $changed_data = $this->makeChangesInFile($data['xml']);
                    }

                    header("Expires: Mon, 1 Apr 1974 05:00:00 GMT");
                    header("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
                    header("Cache-Control: no-cache, must-revalidate");
                    header("Pragma: no-cache");
                    header("Content-type: text/xml; charset=utf-8");
                    header("Content-Disposition: attachment; filename=data.xml");

                    echo $changed_data;
                    exit();
                }
            }
        }
    }

    public function makeChangesInFile($data){
        $root = $data->documentElement;
        $nodesToDelete = array();
        $offers = $root->getElementsByTagName('offer');
        // Loop trough offers
        foreach ($offers as $offer) {
            $param_size = $offer->getElementsByTagName('param')->item(2)->textContent;
            // if many sizes
            if (strpos($param_size, '-') !== false || strpos($param_size, '.') !== false){
                // collect offers with many sizes
                $nodesToDelete[] = $offer;
                // divide the sizes
                $products_size = $this->multiexplode(array(".","-"), $param_size);
                foreach ($products_size as $product_size){
                    // clone offer
                    $new_product = $offer->cloneNode(true);
                    $new_product_offer_id = $new_product->getAttribute('id');
                    // change product size
                    $new_product->getElementsByTagName('param')->item(2)->textContent = $product_size;
                    $new_product->setAttribute('id', $new_product_offer_id . $product_size);
                    // append to DOM
                    $offer->parentNode->appendChild($new_product);
                }
            }
        }

        // delete offers with many sizes from DOM
        foreach ($nodesToDelete as $nodeDelete) {
            $nodeDelete->parentNode->removeChild($nodeDelete);
        }

        foreach ($offers as $offer) {
            $offer_vendor_code = $offer->getElementsByTagName('vendorCode')->item(0)->textContent;
            // set attribute group_id
            $offer->setAttribute('group_id', $offer_vendor_code);
            // add model
            $element = $data->createElement('model', 'Z-'.$offer_vendor_code);
            $offer->appendChild($element);
        }

        // save as XML string
	    return $data->saveXML();
    }

    private function multiexplode ($delimiters, $string) {
        $ready = str_replace($delimiters, $delimiters[0], $string);
        $launch = explode($delimiters[0], $ready);
        return  $launch;
    }

    private function validateImport() {
        if (isset($this->request->files['file']['error']) && $this->request->files['file']['error'] != '0') {
            $this->error['file'] = $this->language->get('error_file');
        }
        return !$this->error;
    }
}
