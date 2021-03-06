<?php

namespace SedpMis\BaseRepository;

class BaseBranchRepositoryEloquent extends BaseRepositoryEloquent implements RepositoryInterface
{
    /**
     * Branch id to be prefix when creating new item to storage.
     * @var int
     */
    protected $branchId;

    /**
     * Set the branch id to be prefixed when creating an id.
     *
     * @param  int   $branchId
     * @return $this
     */
    public function setBranchId($branchId)
    {
        $this->branchId = $branchId;

        return $this;
    }

    /**
     * Alias of setBranchId() method.
     *
     * @param  int   $branchId
     * @return $this
     */
    public function setBranch($branchId)
    {
        return $this->setBranchId($branchId);
    }

    /**
     * Return the branch_id for branch-inserts.
     *
     * @return int
     */
    public function branchId()
    {
        return $this->branchId ?: get_branch_session();
    }

    /**
     * Override.
     * Manipulate model before final save.
     * Setting branchId which is required for branch-inserts.
     *
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function beforeSaveModel($model)
    {
        return method_exists($model, 'setBranchId') ? $model->setBranchId($this->branchId()) : $model;
    }
}
