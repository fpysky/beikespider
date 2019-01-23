<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class ShHousResource extends Resource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'desc' => $this->desc,
            'price' => $this->price,
            'area' => $this->area,
            'area_name' => $this->area_name,
            'visit_time' => $this->visit_time,
            'tags' => $this->tags,
            'house_division' => json_decode($this->house_division,true),
            'community' => json_decode($this->community,true),
            'base_info' => json_decode($this->base_info,true),
            'feature' => json_decode($this->feature,true),
            'pictures' => json_decode($this->pictures,true)
        ];
    }
}
