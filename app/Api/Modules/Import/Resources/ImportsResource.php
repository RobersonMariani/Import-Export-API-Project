<?php

namespace App\Api\Modules\Import\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ImportsResource extends ResourceCollection
{
    public $collects = ImportResource::class;
}
