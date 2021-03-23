<?php

namespace EnamDuaTeknologi\LaravelCrudApi\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

trait Uploadable
{
    public $uploadable = true;
    protected $disk = 'custom';

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
        $data = array_merge($this->uploads ?? [], $this->nullable_uploads ?? []);

        foreach ($this->data as $key => $value) {
            if ($this->$value) {
                $this->removeFile($this->$value);
            }

            if (request()->file($value)) {
                $this->attributes[$value] = $this->upload(request()->file($value));
            }
        }
    }

    public function getUploadsAttribute()
    {
        $uploads = [];

        foreach ($this->image_uploads ?? [] as $value) {
            $uploads[$value] = isset($this->attributes[$value])
                ? env("APP_URL")."/uploads/".$this->attributes[$value]
                : env("APP_URL")."/images/placeholder.jpg";
        }

        foreach ($this->nullable_uploads ?? [] as $value) {
            $uploads[$value] = isset($this->attributes[$value])
                ? env("APP_URL")."/uploads/".$this->attributes[$value]
                : null;
        }

        return $uploads;
    }
}
