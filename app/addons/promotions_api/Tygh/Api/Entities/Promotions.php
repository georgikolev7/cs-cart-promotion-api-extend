<?php

namespace Tygh\Api\Entities;

use Tygh\Api\AEntity;
use Tygh\Api\Response;
use Tygh\Registry;

class Promotions extends AEntity
{
    public function index($id = 0, $params = array())
    {
        $status = Response::STATUS_OK;
        
        if (!empty($id)) {
            $promotion_data = fn_get_promotion_data($id);
            
            if (empty($promotion_data)) {
                $data = $promotion_data;
                $status = Response::STATUS_NOT_FOUND;
            } else {
                $data = $promotion_data;
            }
        } else {
            $items_per_page = $this->safeGet($params, 'items_per_page', Registry::get('settings.Appearance.admin_elements_per_page'));
            list($promotions, $search) = fn_get_promotions($params, $items_per_page, DESCR_SL);
            
            $data = array(
                'promotions' => array_values($promotions),
                'params'   => $search,
            );
        }
        
        return array(
            'status' => $status,
            'data'   => $data,
        );
    }

    public function create($params)
    {
        $status = Response::STATUS_BAD_REQUEST;
        $data = array();
        $valid_params = true;

        unset($params['promotion_id']);

        if (empty($params['promotion'])) {
            $data['message'] = __('api_required_field', array(
                '[field]' => 'promotion'
            ));
            $valid_params = false;
        }

        if ($valid_params) {
            $promotion_id = fn_update_promotion($params, 0);

            if ($promotion_id) {
                $status = Response::STATUS_CREATED;
                $data = array(
                    'promotion_id' => $promotion_id,
                );
            }
        }

        return array(
            'status' => $status,
            'data' => $data
        );
    }

    public function update($id, $params)
    {
        $data = array();
        $status = Response::STATUS_BAD_REQUEST;
        unset($params['promotion_id']);

        $lang_code = $this->getLanguageCode($params);
        $promotion_id = fn_update_promotion($params, $id, $lang_code);

        if ($promotion_id) {
            $status = Response::STATUS_OK;
            $data = array(
                'promotion_id' => $id
            );
        }

        return array(
            'status' => $status,
            'data' => $data
        );
    }

    public function delete($id)
    {
        $data = array();
        $status = Response::STATUS_NOT_FOUND;

        if (fn_delete_promotions($id)) {
            $status = Response::STATUS_NO_CONTENT;
        }

        return array(
            'status' => $status,
            'data' => $data
        );
    }

    public function privileges()
    {
        return array(
            'create' => 'create_things',
            'update' => 'edit_things',
            'delete' => 'delete_things',
            'index'  => 'view_things'
        );
    }
}
