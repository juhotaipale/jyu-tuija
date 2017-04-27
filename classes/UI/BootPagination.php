<?php


namespace UI;

use UI\Pagination;

class BootPagination
{
    public $pagenumber;
    public $pagesize;
    public $totalrecords;
    public $showfirst;
    public $showlast;
    public $paginationcss;
    public $paginationstyle;
    public $defaultUrl;
    public $paginationUrl;
    public $onClick;

    // pager style
    public $prevCss;
    public $nextCss;

    function __construct()
    {
        $this->pagenumber = 1;
        $this->pagesize = 20;
        $this->totalrecords = 0;
        $this->showfirst = true;
        $this->showlast = true;
        $this->paginationcss = "pagination-sm";
        $this->paginationstyle = 0;  // 1: advance, 0: normal, 2: pager

        $this->defaultUrl = "#"; // in case of ajax pagination
        $this->paginationUrl = "#"; // # incase of ajax pagination e.g index.php?p=[p] -->
        $this->onClick = "";

        $this->prevCss = "previous";
        $this->nextCss = "next";
    }

    function process()
    {
        $paginationlst = "";
        $firstbound = 0;
        $lastbound = 0;
        $tooltip = "";

        if ($this->totalrecords > $this->pagesize) {
            $totalpages = ceil($this->totalrecords / $this->pagesize);

            if ($this->pagenumber > 1) {
                if ($this->showfirst && $this->paginationstyle != 2) {
                    $firstbound = 1;
                    $lastbound = $firstbound + $this->pagesize - 1;
                    $tooltip = sprintf(_("näytetään %s&ndash;%s tulosta %s tuloksesta"), $firstbound, $lastbound,
                        $this->totalrecords);
                    // First Link
                    if ($this->defaultUrl == "") {
                        $this->defaultUrl = "#";
                    }
                    $paginationlst .= "<li><a id=\"p_1\" href=\"" . $this->prepareUrl(1) . "\" onclick=\"" . $this->onClickUrl(1) . "\" data-page=\"1\" class=\"pagination-css\" data-toggle=\"tooltip\" title=\"" . $tooltip . "\"><<</a></li>\n";
                }
                $firstbound = (($totalpages - 1) * $this->pagesize);
                $lastbound = $firstbound + $this->pagesize - 1;
                if ($lastbound > $this->totalrecords) {
                    $lastbound = $this->totalrecords;
                }
                $tooltip = sprintf(_("näytetään %s&ndash;%s tulosta %s tuloksesta"), $firstbound, $lastbound,
                    $this->totalrecords);
                // Previous Link Enabled
                if ($this->paginationUrl == "") {
                    $this->paginationUrl = "#";
                }

                $pid = ($this->pagenumber - 1);
                if ($pid < 1) {
                    $pid = 1;
                }
                $prevPageCss = "";
                $prevIcon = "<";
                if ($this->paginationstyle == 2) {
                    if ($this->prevCss != "") {
                        $prevPageCss = " class=\"" . $this->prevCss . "\"";
                    }
                    $prevIcon = "&larr; " . _("Edellinen");
                }
                $paginationlst .= "<li" . $prevPageCss . "><a id=\"pp_" . $pid . "\" href=\"" . $this->prepareUrl($pid) . "\"  onclick=\"" . $this->onClickUrl($pid) . "\" data-page=\"$pid\" data-toggle=\"tooltip\" class=\"pagination-css\" title=\"" . $tooltip . "\">" . $prevIcon . "</a></li>\n";
                // Normal Links
                if ($this->paginationstyle != 2) {
                    $paginationlst .= $this->generate_pagination_links($totalpages, $this->totalrecords,
                        $this->pagenumber, $this->pagesize);
                }

                if ($this->pagenumber < $totalpages) {
                    $paginationlst .= $this->generate_previous_last_links($totalpages, $this->totalrecords,
                        $this->pagenumber, $this->pagesize, $this->showlast);
                }
            } else {
                // Normal Links
                if ($this->paginationstyle != 2) {
                    $paginationlst .= $this->generate_pagination_links($totalpages, $this->totalrecords,
                        $this->pagenumber, $this->pagesize);
                }
                // Next Last Links
                $paginationlst .= $this->generate_previous_last_links($totalpages, $this->totalrecords,
                    $this->pagenumber, $this->pagesize, $this->showlast);
            }
        }
        $paginationCss = "pagination " . $this->paginationcss;
        if ($this->paginationstyle == 2) {
            $paginationCss = "pager";
        }
        return "<div class=\"text-center\"><ul class=\"" . $paginationCss . "\">\n" . $paginationlst . "</ul></div>\n";
    }

    function generate_pagination_links($totalpages, $totalrecords, $pagenumber, $pagesize)
    {
        $script = "";
        $firstbound = 0;
        $lastbound = 0;
        $tooltip = "";
        $lst = new \UI\Pagination();
        if ($this->paginationstyle == 1) {
            $arr = $lst->advance_pagination_links($totalpages, $pagenumber);
        } else {
            $arr = $lst->simple_pagination_links($totalpages, 15, $pagenumber);
        }
        if (count($arr) > 0) {
            foreach ($arr as $item) {
                $firstbound = (($item - 1) * $pagesize) + 1;
                $lastbound = $firstbound + $pagesize - 1;
                if ($lastbound > $totalrecords) {
                    $lastbound = $totalrecords;
                }
                $tooltip = sprintf(_("näytetään %s&ndash;%s tulosta %s tuloksesta"), $firstbound, $lastbound,
                    $this->totalrecords);
                $css = "";
                if ($item == $pagenumber) {
                    $css = " class=\"active\"";
                }
                $script .= "<li" . $css . "><a id=\"pg_" . $item . "\" href=\"" . $this->prepareUrl($item) . "\" onclick=\"" . $this->onClickUrl($item) . "\" class=\"pagination-css\" data-page=\"$item\" data-toggle=\"tooltip\" title=\"" . $tooltip . "\">" . $item . "</a></li>\n";
            }
        }
        return $script;
    }

    function generate_previous_last_links($totalpages, $totalrecords, $pagenumber, $pagesize, $showlast)
    {
        $script = "";
        $firstbound = (($pagenumber) * $pagesize) + 1;
        $lastbound = $firstbound + $pagesize - 1;
        if ($lastbound > $totalrecords) {
            $lastbound = $totalrecords;
        }
        $tooltip = sprintf(_("näytetään %s&ndash;%s tulosta %s tuloksesta"), $firstbound, $lastbound,
            $this->totalrecords);
        // Next Link
        $pid = ($pagenumber + 1);
        if ($pid > $totalpages) {
            $pid = $totalpages;
        }
        $nextPageCss = "";
        $nextPageIcon = ">";
        if ($this->paginationstyle == 2) {
            if ($this->nextCss != "") {
                $nextPageCss = " class=\"" . $this->nextCss . "\"";
            }
            $nextPageIcon = _("Seuraava") . " &rarr;";
        }
        $script .= "<li" . $nextPageCss . "><a id=\"pn_" . $pid . "\" href=\"" . $this->prepareUrl($pid) . "\" onclick=\"" . $this->onClickUrl($pid) . "\" class=\"pagination-css\" data-page=\"$pid\" data-toggle=\"tooltip\" title=\"" . $tooltip . "\">" . $nextPageIcon . "</a></li>\n";
        if ($showlast && $this->paginationstyle != 2) {
            // Last Link
            $firstbound = (($totalpages - 1) * $pagesize) + 1;
            $lastbound = $firstbound + $pagesize - 1;
            if ($lastbound > $totalpages) {
                $lastbound = $totalpages;
            }
            $tooltip = sprintf(_("näytetään %s&ndash;%s tulosta %s tuloksesta"), $firstbound, $lastbound,
                $this->totalrecords);
            $script .= "<li><a id=\"pl_" . $totalpages . "\" href=\"" . $this->prepareUrl($totalpages) . "\" onclick=\"" . $this->onClickUrl($totalpages) . "\" class=\"pagination-css\" data-page=\"$totalpages\" data-toggle=\"tooltip\" title=\"" . $tooltip . "\">>></a></li>\n";
        }
        return $script;
    }

    function prepareUrl($pid)
    {
        if ($this->paginationUrl == "") {
            $this->paginationUrl = "#";
        }
        if ($pid > 0) {
            return preg_replace("/\[p\]/", $pid, $this->paginationUrl);
        } else {
            return preg_replace("/\[p\]/", $pid, $this->defaultUrl);
        }
    }

    function onClickUrl($pid)
    {
        return preg_replace("/\[p\]/", $pid, $this->onClick);
    }
}