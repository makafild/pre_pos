<?php

namespace Core\System\Http\Traits;

use Core\System\Exceptions\CoreExceptionOk;
use Core\System\Exceptions\CoreException;




trait SecureDelete
{
    /**
     * Delete only when there is no reference to other models.
     *
     * @param array $relations
     * @return response
     */

    public function secureDelete($ids, $relations)
    {


        foreach ($ids as $id) {

            $hasRelation = false;

            foreach ($relations as $relation) {

                if ($this->with($relation)->find($id) != null) {

                    if ($this->with($relation)->findOrFail($id)->$relation != null) {

                        if ($this->with($relation)->find($id)->$relation->count() == 0) {
                            $hasRelation = true;
                            if(auth('api')->user()->kind == "admin"){
                                 $this->where('company_id', auth('api')->user()->company_id)->where('id',  $id)->delete();
                            }else{
                                $this->where('id',  $id)->delete();
                            }

                        } else {
                            $hasRelation = false;

                            throw new CoreException('این فیلد دارای اطلاعات در جریان است');
                        }
                    }
                }
            }
        }


        if ($hasRelation) {
            if (auth('api')->user()->company_id) {

                throw new CoreExceptionOk('با موفقیت حذف شد');
            } else {
                $this->where('id',  $id)->delete();
                throw new CoreExceptionOk('با موفقیت حذف شد');
            }
        }
        throw new CoreException('این فیلد دارای اطلاعات در جریان است');
    }
}
