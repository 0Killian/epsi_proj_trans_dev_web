<?php

class input_tuple
{}

function get_inputs($inputs, $type): input_tuple
{
    $data = new input_tuple();

    foreach ($inputs as $input)
    {
        $d = filter_input($type, $input);
        if($d != "")
        {
            $data->$input = $d;
        }
    }

    return $data;
}