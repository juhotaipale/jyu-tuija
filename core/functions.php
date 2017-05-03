<?php

function convertTimestamp($timestamp)
{
    return date('d.m.Y H:i', strtotime($timestamp));
}

function boolean($value)
{
    if ($value) {
        return "<span class='text-success'>" . _("Kyllä") . "</span>";
    }
    return "<span class='text-danger'>" . _("Ei") . "</span>";
}