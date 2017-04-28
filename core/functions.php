<?php

function convertTimestamp($timestamp)
{
    return date('d.m.Y H:i', strtotime($timestamp));
}