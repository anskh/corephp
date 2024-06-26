<?php

declare(strict_types=1);

namespace Corephp\Html;

use Corephp\Helper\Config;
use Corephp\Helper\Html;
use Corephp\Helper\Url;
use Corephp\Model\DbPaginationInterface;

/**
 * Pagination
 * -----------
 * Pagination
 *
 * @author Khaerul Anas <anasikova@gmail.com>
 * @since v1.0.0
 * @package Corephp\Html
 */
class Pagination implements DbPaginationInterface
{
    private int $page_count;
    private int $record_count;
    private int $per_page;
    private int $current_page;
    private int $offset;
    private string $html;
    private array $query = [];

    /**
     * __construct
     *
     * @param  mixed $total_records
     * @param  mixed $per_page
     * @return void
     */
    public function __construct(int $total_records, ?int $per_page = null)
    {
        $this->record_count = $total_records;

        $parsed_url = Url::getParseUrl();
        if (isset($parsed_url['query'])) {
            parse_str($parsed_url["query"], $this->query);
        }

        $this->current_page = isset($this->query['page']) ? intval($this->query['page']) : 1;
        if(is_null($per_page)){
            $per_page = Config::get('view.pagination.per_page');
        }
        $this->per_page = isset($this->query['limit']) ? intval($this->query['limit']) : $per_page;
        $this->page_count = intval($this->record_count / $this->per_page);
        if (($this->record_count % $this->per_page) > 0) {
            $this->page_count += 1;
        }
        if ($this->current_page > $this->page_count) {
            $this->current_page = $this->page_count;
        }
        $this->offset = ($this->current_page - 1) * $this->per_page;

        $html = $this->begin();

        if ($this->page_count > 7) {
            if ($this->current_page === 1) {
                $html .= $this->prevButtonDisabled();
                $html .= $this->currentPageActive($this->current_page);
            } else {
                $html .= $this->prevButtonActive($this->current_page - 1);
                $html .= $this->pageButtonActive(1);
            }

            if ($this->page_count - $this->current_page > 3) {
                if ($this->current_page > 4) {
                    $html .= $this->dotPage();
                    $html .= $this->pageButtonActive($this->current_page - 1);
                    $html .= $this->pageButtonSelected($this->current_page);
                    $html .= $this->pageButtonActive($this->current_page + 1);
                } else {
                    for ($page_no = 2; $page_no <= 5; $page_no++) {
                        if ($this->current_page === $page_no) {
                            $html .= $this->pageButtonSelected($page_no);
                        } else {
                            $html .= $this->pageButtonActive($page_no);
                        }
                    }
                }
            }

            if ($this->page_count - $this->current_page < 4) {
                $html .= $this->dotPage();
                for ($page_no = $this->page_count - 4; $page_no <= $this->page_count - 1; $page_no++) {
                    if ($this->current_page === $page_no) {
                        $html .= $this->pageButtonSelected($page_no);
                    } else {
                        $html .= $this->pageButtonActive($page_no);
                    }
                }
            } else {
                $html .= $this->dotPage();
            }

            if ($this->current_page === $this->page_count) {
                $html .= $this->pageButtonSelected($this->current_page);
                $html .= $this->nextButtonDisabled();
            } else {
                $html .= $this->pageButtonActive($this->page_count);
                $html .= $this->nextButtonActive($this->current_page + 1);
            }
        } else {
            if ($this->current_page == 1) {
                $html .= $this->prevButtonDisabled();
            } else {
                $html .= $this->prevButtonActive($this->current_page - 1);
            }
            for ($page_no = 1; $page_no <= $this->page_count; $page_no++) {
                if ($this->current_page === $page_no) {
                    $html .= $this->pageButtonSelected($page_no);
                } else {
                    $html .= $this->pageButtonActive($page_no);
                }
            }
            if ($this->current_page === $this->page_count) {
                $html .= $this->nextButtonDisabled();
            } else {
                $html .= $this->nextButtonActive($this->current_page + 1);
            }
        }

        $html .= $this->end();

        $this->html = $html;
    }

    /**
     * begin
     *
     * @return string
     */
    private function begin(): string
    {
        return '<nav><ul class="pagination">';
    }

    /**
     * end
     *
     * @return string
     */
    private function end(): string
    {
        return '</ul></nav>';
    }
    /**
     * currentPageActive
     *
     * @param  mixed $page_no
     * @return string
     */
    private function currentPageActive(int $page_no): string
    {
        return '<li class="page-item active" aria-current="page"><span class="page-link">' . $page_no . '</span></li>';
    }
    /**
     * prevButtonActive
     *
     * @param  mixed $page_no
     * @return string
     */
    private function prevButtonActive(int $page_no): string
    {
        return '<li class="page-item"><a class="page-link" href="' . $this->getUrl($page_no) . '"><span aria-hidden="true">&laquo;</span></a></li>';
    }

    /**
     * prevButtonDisabled
     *
     * @return string
     */
    private function prevButtonDisabled(): string
    {
        return '<li class="page-item disabled"><span class="page-link"><span aria-hidden="true">&laquo;</span></span></li>';
    }
    /**
     * pageButtonActive
     *
     * @param  mixed $page_no
     * @return string
     */
    private function pageButtonActive(int $page_no): string
    {
        return '<li class="page-item"><a class="page-link" href="' . $this->getUrl($page_no) . '">' . ($page_no) . '</a></li>';
    }
    /**
     * dotPage
     *
     * @return string
     */
    private function dotPage(): string
    {
        return  '<li class="page-item"><span class="page-link">...</span></li>';
    }
    /**
     * pageButtonSelected
     *
     * @param  mixed $page_no
     * @return string
     */
    private function pageButtonSelected(int $page_no): string
    {
        return '<li class="page-item active" aria-current="page"><span class="page-link">' . ($page_no) . '</span></li>';
    }
    /**
     * nextButtonDisabled
     *
     * @return string
     */
    private function nextButtonDisabled(): string
    {
        return '<li class="page-item disabled"><span class="page-link"><span aria-hidden="true">&raquo;</span></span></li>';
    }
    /**
     * nextButtonActive
     *
     * @param  mixed $page_no
     * @return string
     */
    private function nextButtonActive(int $page_no): string
    {
        return '<li class="page-item"><a class="page-link" href="' . $this->getUrl($page_no) . '"><span aria-hidden="true">&raquo;</span></a></li>';
    }
    /**
     * getUrl
     *
     * @param  mixed $page_no
     * @return string
     */
    private function getUrl(int $page_no): string
    {
        $parsed_url = Url::getParseUrl();

        if ($page_no == 1) {
            unset($this->query['page']);
        } else {
            $this->query["page"] = $page_no;
        }

        $query = htmlentities(http_build_query($this->query));

        return ($query) ? $parsed_url['path'] . '?' . $query : $parsed_url['path'];
    }

    /**
     * __tostring
     *
     * @return string
     */
    public function __tostring(): string
    {
        return $this->html;
    }
    /**
     * pageCount
     *
     * @return int
     */
    public function pageCount(): int
    {
        return $this->page_count;
    }
    /**
     * recordCount
     *
     * @return int
     */
    public function recordCount(): int
    {
        return $this->record_count;
    }
    /**
     * perPage
     *
     * @return int
     */
    public function perPage(): int
    {
        return $this->per_page;
    }
    /**
     * currentPage
     *
     * @return int
     */
    public function currentPage(): int
    {
        return $this->current_page;
    }
    /**
     * offset
     *
     * @return int
     */
    public function offset(): int
    {
        return $this->offset;
    }
    /**
     * html
     *
     * @return string
     */
    public function html(): string
    {
        return $this->html;
    }
}
