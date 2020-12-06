<?php
namespace bricksasp\base;

use bricksasp\base\models\File;
use Yii;
use yii\data\ActiveDataProvider;
use yii\validators\FileValidator;
use yii\web\UploadedFile;

/**
 * 文件上传操作
 */
class FileAction extends \yii\base\Action
{
    use BaseTrait;

	public $config = [
		'allowFiles' => ['png', 'jpg', 'jpeg', 'gif','mp4'/*, 'bmp', 'flv', 'swf', 'mkv', 'avi', 'rm', 'rmvb', 'mpeg', 'mpg', 'ogg', 'ogv', 'mov', 'wmv', 'mp4', 'webm', 'mp3', 'wav', 'mid', 'rar', 'zip', 'tar', 'gz', '7z', 'bz2', 'cab', 'iso', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'pdf', 'txt', 'md', 'xml',*/
		],
		'maxSize' => 10048000,
	];

	public $validatorConfig = [];

	public $base_path = null;
	public $file_path = null;
	public $temp_path = null;

	public function init() {
		Yii::$app->request->enableCsrfValidation = false;
		if (!$this->base_path) {
			$this->base_path = Yii::$app->basePath . '/web';
		}

		if (!$this->file_path) {
			$this->file_path = '/file/' . date('Y') . '/' . date('m');
		}

		if (!$this->temp_path) {
			$this->temp_path = '/file/temp';
		}

	}

	public function run() {
		switch ($this->id) {
		case 'fileupload':
			$res = $this->upload();
			break;
        case 'fileuploads':
            $res = $this->uploads();
            break;
		case 'filedelete':
			$res = $this->delete();
			break;
		default:
			$res = $this->fail();
			break;
		}
		return $res;
	}

	/**
	 * 单文件上传
	 */
	protected function upload() {
		$file = $this->checkFile();
		$file_id = Tools::get_sn(10);
		$dir = $this->base_path . $this->file_path;
		Tools::make_dir($dir);

		$file_url = $this->file_path . '/' . md5($file_id) . '.' . $file->extension;
		$real_path = $dir . '/' . md5($file_id) . '.' . $file->extension;
		if ($file->saveAs($real_path)) {
			if (Yii::$app->request->post('oldFile')) {
				Tools::deleteFile($this->base_path . str_replace(Yii::$app->params['globalParams']['fileBaseUrl'], '', Yii::$app->request->post('oldFile')));
			}
			return $this->success(['file_url' => Yii::$app->params['globalParams']['fileBaseUrl'] . $file_url]);
		}

		return $this->fail();
	}


    /**
     * 多件上传
     */
    protected function uploads() {
        $files = $this->checkFiles();
        $file_urls = [];
        foreach($files as $k=>$file){
            $file_id = Tools::get_sn(10);
            $dir = $this->base_path . $this->file_path;
            Tools::make_dir($dir);

            $file_url = $this->file_path . '/' . md5($file_id) . '.' . $file->extension;
            $real_path = $dir . '/' . md5($file_id) . '.' . $file->extension;
            if ($file->saveAs($real_path)) {
                if (Yii::$app->request->post('oldFile')) {
                    Tools::deleteFile($this->base_path . str_replace(Yii::$app->params['globalParams']['fileBaseUrl'], '', Yii::$app->request->post('oldFile')));
                }
                $file_urls[] = Yii::$app->params['globalParams']['fileBaseUrl'] . $file_url;
            }
        }
        if($file_urls){
            return $this->success($file_urls);
        }
        return $this->fail();
    }



	/**
	 * 文件检测
	 */
	protected function checkFile($fname = 'file') {
		$file = UploadedFile::getInstanceByName($fname); //接收文件
		$validator = new \yii\validators\FileValidator($this->validatorConfig);
		$validator->extensions = $this->config['allowFiles'];
		$validator->maxSize = $this->config['maxSize'];
		if (!$validator->validate($file, $error)) {
			Tools::breakOff($error);
		}

		return $file;
	}


    /**
     * 多文件检测
     */
    protected function checkFiles($fname = 'file') {
        $files = UploadedFile::getInstancesByName($fname); //接收文件
        $validator = new \yii\validators\FileValidator($this->validatorConfig);
        $validator->extensions = $this->config['allowFiles'];
        $validator->maxSize = $this->config['maxSize'];
        foreach($files as $k=>$file){
            if (!$validator->validate($file, $error)) {
                Tools::breakOff($error);
            }
        }


        return $files;
    }
}