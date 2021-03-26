/**

 @Name：组织架构管理
 @Author：illmy
 @License：LPPL
    
 */

layui.define(['admin','table','jqZtreeCore'], function(exports){ 
    var $ = layui.$
    ,admin = layui.admin
    ,view = layui.view
    ,table = layui.table
    ,form = layui.form
    ,layer = layui.layer
    ,setter = layui.setter
    ,ztree = layui.jqZtreeCore;
    
    //配置信息
    var setting = {
      view: {
				expandSpeed: ""
			},
      async: {
				enable: false,
				url: getUrl,
        dataFilter: ajaxDataFilter
			},
      data: {
        simpleData: {
          enable: true,
          pIdKey: "parent_id"
        }
      },
      edit : {
        enable : true,
        renameTitle : "编辑节点名称",
        showRenameBtn : true,
      },
      callback: {
        onClick: zTreeOnClick,
        beforeRemove: beforeRemove,
        beforeEditName: zTreeBeforeEditName,
      }
    };
    
    //获取登录用户信息
    var loginTbData = layui.data('userData')
    ,departmentList = 'department/list'
    ,userListUrl = 'user/list'
    ,host = ''
    ,url = departmentList;

    //拉取信息
    admin.req({
      url: url
      ,done: function(res){
        if(res.code != '0'){
          layer.msg('获取数据失败');
          return false;
        }
        var zNodes = res.data;
        $.each(zNodes,function(it,item) {
          item.isParent = true;
        });
        console.log(zNodes);
        var obj = ztree.zTree.init($("#treeDemo"), setting, zNodes);
        obj.expandAll(true);
        var node = obj.getNodeByParam("id", (zNodes[0]).id, null);
        setting.callback.onClick(null, obj.setting.treeId, node);
        // var url = returnUrl('1');
        // getDepartmentList(url);
      }
    });
    
    
    //返回请求连接
    function returnUrl(treeNode) {
      if (treeNode == '1') {
        return departmentList;
      }

      var param = "department_id="+treeNode.id;
      return host+userListUrl + '?' + param;
    }

    //异步加载数据
    function getUrl(treeId, treeNode) {
      return returnUrl(treeNode);
		}

    //异步数据处理
    function ajaxDataFilter(treeId, parentNode, responseData) {
      var zNodes = responseData.data;
      $.each(zNodes,function(it,item) {
        item.isParent = true;
      });
      return zNodes;
    };

    //点击事件
    function zTreeOnClick(event, treeId, treeNode) {
      console.log(treeNode);
      $("#res-department-name").html('【'+treeNode.name+'】');
      var up_departid = treeNode.id;

      $("#department-form-departid").val(up_departid);
      var url = returnUrl(treeNode);
      getDepartmentList(url);
    }

    //由于是数据交互是非阻塞的 手动删除节点
    function beforeRemove(treeId, treeNode) {
      var zTree = $.fn.zTree.getZTreeObj("treeDemo");
      zTree.selectNode(treeNode);

      if(treeNode.level == '0'){
        layer.msg('不能删除根节点');
        return false;
      }
      deleteDepartment(treeNode.id);

      return false;
    }

    function zTreeBeforeEditName(treeId, treeNode) {
      editorDepartment(treeNode.id);
      return !treeNode.isParent;
    }
    
    /**************打开弹窗 ***************/
    function editorDepartment(id) {
      admin.popup({
        title: '编辑部门',
        area: ['675px', '550px'],
        id: 'LAY-popup-user-add',
        btn: ['确定', '取消'],
        btnAlign: 'c',
        success: function (layero, index) {
            layui.admin.INDEXS = index;
            view(this.id).render('department/editor', {
                id: id
            }).done(function () {
                form.render(null, 'department-update-form-update');
            });
        },
        yes: function (index, layero) {
            //点击确认触发 iframe 内容中的按钮提交
            var submit = $("#department-update-submit-update");
            submit.click();
        }
      });
    }

    function deleteDepartment(id) {
      let url = 'department/delete?id='+id
      layer.confirm('确定删除吗？', function(index){
        admin.req({
          url: url
          ,done: function(res){
            if(res.code != '0'){
              layer.msg('删除失败');
              return false;
            }
            klt_department.deleteNode(id);
            layer.msg('删除成功', {
              offset: '15px'
              ,icon: 1
              ,time: 1000
            }, function(){
              layer.close(index); //执行关闭 
            });
          }
        });
      });
    }

    /**************树结构结束**************/

    table.set({
      response: {
        statusName: 'code' //规定数据状态的字段名称，默认：code
        ,statusCode: '0' //规定成功的状态码，默认：0
        ,countName: 'count' //规定数据总数的字段名称，默认：count
      } 
    });
    
    function getDepartmentList(url) {
      //部门管理
      table.render({
        elem: '#Filter-table-department'
        ,url: url //模拟接口
        ,where: {
          "X-Token": layui.data(setter.tableName)[setter.request.tokenName]
        }
        ,cols: [[
          {field: 'nickname', title: '员工姓名'}
          ,{field: 'manager_id', title: '是否经理', templet:function(d){
            if(d.manager_id > 0){return '是';}else{return '否';}
          }}
          ,{field: 'state', title: '部门类型', templet:function(d){
            if(d.state==1){return '可用';}else{return '禁用';}
          }}
          ,{field: 'created_at', title: '创建时间', sort: true}
          ,{title: '操作', width: 150, align: 'center', fixed: 'right', toolbar: '#ID_table_toolbar_department'}
        ]]
        ,page: true
        ,limit: 30
        ,height: 'full-340'
        ,text: {
          none: '暂无相关数据' //默认：无数据。注：该属性为 layui 2.2.5 开始新增
        }
      });
    }
    
    //监听工具条
    table.on('tool(Filter-table-department)', function(obj){
      var data = obj.data;
      if(obj.event === 'del'){
        layer.confirm('确定删除吗？', function(index){
            admin.req({
              url: 'user/delete?id='+data.id
              ,done: function(res){
                if(res.code != '0'){
                  layer.msg('删除失败');
                  return false;
                }
                klt_department.clickNode(data.department_id);

                layer.msg('删除成功', {
                  offset: '15px'
                  ,icon: 1
                  ,time: 1000
                }, function(){
                  layer.close(index); //执行关闭 
                });
              }
            });
        });
      }else if(obj.event === 'edit'){
        admin.popup({
          title: '编辑员工',
          area: ['675px', '550px'],
          id: 'LAY-popup-user-add',
          btn: ['确定', '取消'],
          btnAlign: 'c',
          success: function (layero, index) {
              layui.admin.INDEXS = index;
              view(this.id).render('member/editor', {
                  id: data.id
              }).done(function () {
                  form.render(null, 'member-update-form-update');
              });
          },
          yes: function (index, layero) {
              //点击确认触发 iframe 内容中的按钮提交
              var submit = $("#member-update-submit-update");
              submit.click();
          }
        });
      }
    });

    var klt_department = function() {};

    //新增节点
    klt_department.addNode = function(pid,node) {
      console.log(pid);
      var treeObj = $.fn.zTree.getZTreeObj("treeDemo");
      var pnode = treeObj.getNodeByParam("id", pid, null);

      childnode = pnode.children;
      if (childnode && childnode.length > 0) {
        node.isParent = true;
        newNode = treeObj.addNodes(pnode,0, node);
      }
      treeObj.expandAll(true);   
    };

    //修改节点
    klt_department.updateNode = function(id,node) {
      var treeObj = $.fn.zTree.getZTreeObj("treeDemo");
      var newnode = treeObj.getNodeByParam("id", id, null);
      newnode.name = node.name;
      newNode = treeObj.updateNode(newnode);
    };

    //删除节点
    klt_department.deleteNode = function(id) {
      var treeObj = $.fn.zTree.getZTreeObj("treeDemo");
      var newnode = treeObj.getNodeByParam("id", id, null);
      treeObj.removeNode(newnode);
      treeObj.expandAll(true);
    };

    klt_department.clickNode = function(id) {
      var obj = $.fn.zTree.getZTreeObj("treeDemo");
      var node = obj.getNodeByParam("id", id, null);
      setting.callback.onClick(null, obj.setting.treeId, node);
    }


    exports('department', klt_department);
  });