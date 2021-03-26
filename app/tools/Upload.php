<?php

namespace app\tools;

use Upload\Storage\FileSystem;
use Upload\Validation\Mimetype;
use Upload\Validation\Size;
use Upload\File;

/**
 * 文件上传
 */
class Upload
{
    protected $storage = '../runtime/storage/upload/';

    protected $path;

    protected $fileSystem;

    protected $mimetype = ['image/gif', 'image/png', 'image/jpg'];

    protected $size = '2M';

    protected $errors = '';

    public function __construct(string $updateField = 'file', string $path = '')
    {
        $this->path = $this->storage . $path;
        $this->checkDir();
        $storage = new FileSystem($this->path);
        $fileSystem = new File($updateField, $storage);
        $newFilename = uniqid();
        $fileSystem->setName($newFilename);
        $this->fileSystem = $fileSystem;
    }

    protected function validate(array $mimetype = [], string $size = '')
    {
        $mimetype = !empty($mimetype) ? $mimetype : $this->mimetype;
        $size = !empty($size) ? $size : $this->size;
        $this->fileSystem->addValidations([
            new Mimetype($mimetype),

            new Size($size)
        ]);
    }

    protected function checkDir()
    {
        if (!is_dir($this->path)) {
            mkdir($this->path, 0777, true);
        }
    }

    /**
     * 上传文件
     *
     * @param array $mimetype
     * @param string $size
     * @return array|boolean
     */
    public function move(array $mimetype = [], string $size = '')
    {
        $this->validate($mimetype, $size);
        try {
            $this->fileSystem->upload();
            $status = true;
        } catch (\Exception $e) {
            $this->errors = $this->fileSystem->getErrors();
            $status = false;
        }
        if ($status) {
            $data = [
                'name'       => $this->fileSystem->getNameWithExtension(),
                'extension'  => $this->fileSystem->getExtension(),
                'mime'       => $this->fileSystem->getMimetype(),
                'size'       => $this->fileSystem->getSize(),
                'path'       => $this->path
            ];
            return $data;
        } else {
            return false;
        }
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
