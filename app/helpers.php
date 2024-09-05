<?php 
function storeImage($image) 
{
    $imageName = time().'.'.$image->extension();
    Storage::disk('public')->putFileAs('images',$image, $imageName);
    return $imageName ;
}