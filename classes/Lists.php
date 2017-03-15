<?php namespace Clake\UserExtended\Classes;

use Illuminate\Support\Collection;
/**
 * User Extended by Shawn Clake
 * Class Lists
 * User Extended is licensed under the MIT license.
 *
 * @author Shawn Clake <shawn.clake@gmail.com>
 * @link https://github.com/ShawnClake/UserExtended
 *
 * @license https://github.com/ShawnClake/UserExtended/blob/master/LICENSE MIT
 * @package Clake\UserExtended\Classes
 */
class Lists extends Collection
{
    private $list;
    private $limit = 10;
    /**
     * Creates a new list object
     * Lists constructor.
     * @param Collection|null $c
     */
    public function __construct(Collection $c = null)
    {
        $this->disableLimiting();
        if($c != null)
            $this->list = $c;
        else
            $this->list = new Collection;
    }
    /**
     * Factory method to create a new list
     * @param Collection|null $c
     * @return static
     */
    public static function create(Collection $c = null)
    {
        $o = new static();
        $o->disableLimiting();
        if($c != null)
            $o->list = $c;
        else
            $o->list = new Collection;
        return $o;
    }
    /**
     * Intelligent merging.
     * Provides a workaround for a bug when the collection is empty
     * @param Collection $c
     * @return $this
     */
    public function mergeList(Collection $c)
    {
        if($this->list->isEmpty())
            $this->list = $c;
        else
            $this->list->merge($c);
        return $this;
    }
    /**
     * Returns the entire list regardless of limiting
     * @return Collection
     */
    public function allList()
    {
        return $this->list;
    }
    /**
     * Returns the list taking into account limiting
     * @return Collection|static
     */
    public function getList()
    {
        if($this->limit != -1)
            return $this->list->take($this->limit);
        else
            return $this->allList();
    }
    /**
     * Sets the list data
     * @param Collection $c
     * @return $this
     */
    public function setList(Collection $c)
    {
        $this->list = $c;
        return $this;
    }
    /**
     * Pushses a new set of data to the list.
     * Takes into account limiting
     * @param Collection $c
     * @return $this
     */
    public function pushList(Collection $c)
    {
        if($this->limit != -1)
        {
            $newCount = $c->count();
            $currentCount = $this->list->count();
            if($newCount + $currentCount > $this->limit)
            {
                $difference = $newCount + $currentCount - $this->limit;
                if($difference > $this->limit)
                {
                    $this->list = null;
                    $this->list = $c->slice($difference - $this->limit);
                    return $this;
                }
                else
                {
                    $this->list = $this->list->slice($difference);
                }
            }
        }
        $this->mergeList($c);
        return $this;
    }
    /**
     * Returns true if the list is at its limit
     * @return bool
     */
    public function isFull()
    {
        return $this->list->count() >= $this->limit;
    }
    /**
     * Set the limit for the list
     * @param int $limit
     * @return $this
     */
    public function setLimit($limit = 10)
    {
        $this->limit = $limit;
        return $this;
    }
    /**
     * Disable limiting for this list
     */
    public function disableLimiting()
    {
        $this->limit = -1;
    }
    /**
     * Enable limiting for this list
     * @param int $limit
     */
    public function enableLimiting($limit = 10)
    {
        $this->limit = $limit;
    }
}