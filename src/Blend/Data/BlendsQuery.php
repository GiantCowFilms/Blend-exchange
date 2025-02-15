<?php declare(strict_types = 1);

namespace BlendExchange\Blend\Data;
use BlendExchange\Blend\Model\BlendFile;

final class BlendsQuery
{
    private $query;
    private $errors = [];
    private $page;
    private $orderBy;
    private $owner = null;

    public function __construct(VisibleBlendsQuery $query, string $orderBy, int $page)
    {
        $this->orderBy = $orderBy;
        $this->page = $page;
        $query->buildQuery();
        $this->query = $query->getQuery();
    }

    public function buildQuery() {
        $this->query = $this->query
            ->where('fileGoogleId','!=',null)->withCount('favorites');
        // Use cached value instead
        //->withUniqueCount('views','ip')->withUniqueCount('downloads','ip');
        switch($this->orderBy)
        {
            case 'date':
                $this->query = $this->query->orderBy('date','DESC');
                break;
            case 'views':
                //$this->query = $this->query->orderBy('views_count','DESC');
                $this->query = $this->query->orderBy('view_count_cache','DESC');
                break;
            case 'downloads':
                //$this->query = $this->query->orderBy('downloads_count','DESC');
                $this->query = $this->query->orderBy('download_count_cache','DESC');
                break;
            case '':
                break;
            default:
                $this->errors['sort'] = 'Invalid ordering parameter.';
        }

        if ($this->owner !== null) {
            $this->query = $this->query->where('owner','=',$this->owner);
        }
    }

    public function hasErrors() {
        return count($this->errors) > 0;
    }

    public function getErrors() {
        return $this->errors;
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function getPage() {
        return $this->page;
    }

    public function execute()
    {
        return $this->getQuery()->paginate(25,['*'], 'page', $this->page);
    }

    public function setOwner($owner) {
        $this->owner = $owner;
    }
}