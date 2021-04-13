<?php

namespace EnamDuaTeknologi\LaravelCrudApi\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

trait Uploadable
{
    public $uploadable = true;
    public $input_files = [];
    protected $disk = 'custom';
    protected $default_image_uploads = ['image_url'];
    protected $default_nullable_uploads = [];

    public function upload(UploadedFile $uploadedFile)
    {
        $array = explode('\\', __CLASS__);
        $folder = str_plural(strtolower(end($array)));
        $id = uniqid().'.'.$uploadedFile->getClientOriginalExtension();

        $uploadedFile->storeAs($folder, $id, $this->disk);

        return $folder.'/'.$id;
    }

    public function removeFile($path)
    {
        return Storage::disk($this->disk)->delete($path);
    }

    public function bulkUploads()
    {
        $data = array_merge(
            $this->image_uploads ?? $this->default_image_uploads,
            $this->nullable_uploads ?? $this->default_nullable_uploads
        );

        foreach ($data as $key => $value) {
            if ($this->$value) {
                $this->removeFile($this->$value);
            }

            if (request($value) instanceof UploadedFile) {
                $this->input_files[$value] = $this->upload(request($value));
            }
        }
    }

    public function getUploadsAttribute()
    {
        $uploads = [];

        foreach ($this->image_uploads ?? $this->default_image_uploads as $value) {
            if (array_key_exists($value, $this->attributes)) {
                $uploads[$value] = ($this->attributes[$value])
                    ? env("APP_URL")."/uploads/".$this->attributes[$value]
                    : env("APP_URL")."/images/placeholder.jpg";
            }
        }

        foreach ($this->nullable_uploads ?? $this->default_nullable_uploads as $value) {
            if (array_key_exists($value, $this->attributes)) {
                $uploads[$value] = ($this->attributes[$value])
                    ? env("APP_URL")."/uploads/".$this->attributes[$value]
                    : null;
            }
        }

        return $uploads;
    }
}
