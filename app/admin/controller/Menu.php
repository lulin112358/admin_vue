<?php


namespace app\admin\controller;


use app\admin\service\MenuService;
use app\Code;
use app\mapper\MenusMapper;
use app\validate\MenuValidate;

class Menu extends Base
{
    /**
     * 获取所有菜单
     * @param MenuService $service
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function allMenu(MenuService $service) {
        try {
            $data = $service->allMenu();
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($data);
    }

    /**
     * 所有菜单树形结构
     * @param MenuService $service
     */
    public function allMenuTree(MenuService $service) {
        try {
            $data = $service->menuTree();
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($data);
    }

    /**
     * 获取用户菜单
     * @param MenuService $service
     */
    public function userMenu(MenuService $service) {
        try {
            $data = $service->getMenuByUser();
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }
        $this->ajaxReturn($data);
    }

    /**
     * 获取单个数据
     * @param MenuService $service
     * @param MenuValidate $validate
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getMenu(MenuService $service, MenuValidate $validate) {
        $param = input('param.');
        if (!$validate->scene('getOne')->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());

        try {
            $data = $service->getMenu($param['id']);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }

        if (!$data)
            $this->ajaxReturn(Code::ERROR, '暂无数据');

        $this->ajaxReturn($data);
    }

    /**
     * 添加菜单
     * @param MenuService $service
     * @param MenuValidate $validate
     */
    public function addMenu(MenuService $service, MenuValidate $validate) {
        $param = input('param.');
        if (!$validate->scene('addMenu')->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());

        try {
            $res = $service->addMenu($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }

        if (!$res)
            $this->ajaxReturn(Code::ERROR, '添加失败');

        $this->ajaxReturn('添加成功');
    }


    /**
     * 更新菜单
     * @param MenuService $service
     * @param MenuValidate $validate
     */
    public function updateMenu(MenuService $service, MenuValidate $validate) {
        $param = input('param.');
        if (!$validate->scene('saveMenu')->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());

        try {
            $res = $service->updateMenu($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }

        if ($res === false)
            $this->ajaxReturn(Code::ERROR, '更新失败');

        $this->ajaxReturn('更新成功');
    }



    public function deleteMenu(MenuService $service, MenuValidate $validate) {
        $param = input('param.');
        if (!$validate->scene('delMenu')->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());

        try {
            $res = $service->deleteMenu($param['id']);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }

        if ($res === false)
            $this->ajaxReturn(Code::ERROR, '删除失败');

        $this->ajaxReturn(Code::SUCCESS, '删除成功');
    }


    /**
     * 更新显示状态/排序
     * @param MenuService $service
     * @param MenuValidate $validate
     */
    public function updateData(MenuService $service, MenuValidate $validate) {
        $param = input('param.');
        if (!$validate->scene('update')->check($param))
            $this->ajaxReturn(Code::PARAM_VALIDATE, $validate->getError());

        try {
            $res = $service->updateMenu($param);
        }catch (\Exception $exception) {
            $this->ajaxReturn(Code::ERROR, $exception->getMessage());
        }

        if ($res === false)
            $this->ajaxReturn(Code::ERROR, '操作失败');

        $this->ajaxReturn(Code::SUCCESS, '操作成功');
    }
}