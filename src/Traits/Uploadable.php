<?php

namespace EnamDuaTeknologi\LaravelCrudApi\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

trait Uploadable
{
    public $uploadable = true;

    public function upload(UploadedFile $uploadedFile)
    {
        $array = explode('\\', __CLASS__);
        $folder = str_plural(strtolower(end($array)));
        $id = uniqid().'.'.$uploadedFile->getClientOriginalExtension();

        $uploadedFile->storeAs($folder, $id, 'custom');

        return $folder.'/'.$id;
    }

    public function removeFile($path)
    {
        return Storage::disk('custom')->delete($path);
    }

    public function setImageUrlAttribute($value)
    {
        if ($this->image_url) {
            $this->removeFile($this->image_url);
        }

        $this->attributes['image_url'] = $this->upload($value);
    }

    public function getImageAttribute()
    {
        return isset($this->attributes['image_url'])
            ? env("APP_URL")."/uploads/".$this->attributes['image_url']
            : env("APP_URL")."/images/placeholder.jpg";
    }
}
