<?php

function convertTimestamp($timestamp)
{
    return date('d.m.Y H:i', strtotime($timestamp));
}

function boolean($value)
{
    if ($value) {
        return _("Kyllä");
    }
    return _("Ei");
}