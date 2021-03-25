/**

 @Name：组织架构管理
 @Author：illmy
 @Site：http://klt.hoocent.com:8088
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
				enable: true,
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
        beforeRename: beforeRename,
        beforeEditName: zTreeBeforeEditName,
        beforeDrag: beforeDrag, //拖拽前：捕获节点被拖拽之前的事件回调函数，并且根据返回值确定是否允许开启拖拽操作
        beforeDrop: beforeDrop, //拖拽中：捕获节点操作结束之前的事件回调函数，并且根据返回值确定是否允许此拖拽操作
        beforeDragOpen: beforeDragOpen, //拖拽到的目标节点是否展开：用于捕获拖拽节点移动到折叠状态的父节点后，即将自动展开该父节点之前的事件回调函数，并且根据返回值确定是否允许自动展开操作
        onDrag: onDrag, //捕获节点被拖拽的事件回调函数
        onDrop: onDrop, //捕获节点拖拽操作结束的事件回调函数
        onExpand: onExpand //捕获节点被展开的事件回调函数
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
      var level = treeNode.level;
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

    function beforeExpand(treeId, treeNode) {
			if (!treeNode.isAjaxing) {
				startTime = new Date();
				treeNode.times = 1;
				ajaxGetNodes(treeNode, "refresh");
				return true;
			} else {
				layer.msg("zTree 正在下载数据中，请稍后展开节点。。。");
				return false;
			}
		}

    function ajaxGetNodes(treeNode, reloadType) {
			var zTree = $.fn.zTree.getZTreeObj("treeDemo");
			if (reloadType == "refresh") {
				treeNode.icon = "";
				zTree.updateNode(treeNode);
			}
			zTree.reAsyncChildNodes(treeNode, reloadType, true);
		}

    //点击事件
    function zTreeOnClick(event, treeId, treeNode) {
      $("#res-department-name").html('【'+treeNode.name+'】');
      var level = treeNode.level;
      var rid = treeNode.rid; 
      var up_departid = treeNode.id;

      $("#department-form-rid").val(rid);
      $("#department-form-departid").val(up_departid);
      var url = returnUrl(treeNode);
      getDepartmentList(url);
    }

    //拖动排序
    function dropPrev(treeId, nodes, targetNode) {
      var pNode = targetNode.getParentNode();
      if (pNode && pNode.dropInner === false) {
        return false;
      } else {
        for (var i=0,l=curDragNodes.length; i<l; i++) {
          var curPNode = curDragNodes[i].getParentNode();
          if (curPNode && curPNode !== targetNode.getParentNode() && curPNode.childOuter === false) {
            return false;
          }
        }
      }
      return true;
    }
    function dropInner(treeId, nodes, targetNode) {
      if (targetNode && targetNode.dropInner === false) {
        return false;
      } else {
        for (var i=0,l=curDragNodes.length; i<l; i++) {
          if (!targetNode && curDragNodes[i].dropRoot === false) {
            return false;
          } else if (curDragNodes[i].parentTId && curDragNodes[i].getParentNode() !== targetNode && curDragNodes[i].getParentNode().childOuter === false) {
            return false;
          }
        }
      }
      return true;
    }
    function dropNext(treeId, nodes, targetNode) {
      var pNode = targetNode.getParentNode();
      if (pNode && pNode.dropInner === false) {
        return false;
      } else {
        for (var i=0,l=curDragNodes.length; i<l; i++) {
          var curPNode = curDragNodes[i].getParentNode();
          if (curPNode && curPNode !== targetNode.getParentNode() && curPNode.childOuter === false) {
            return false;
          }
        }
      }
      return true;
    }
   
    var log, className = "dark", curDragNodes, autoExpandNode;
    function beforeDrag(treeId, treeNodes) {
      className = (className === "dark" ? "":"dark");
      for (var i=0,l=treeNodes.length; i<l; i++) {
        if (treeNodes[i].drag === false) {
          curDragNodes = null;
          return false;
        } else if (treeNodes[i].parentTId && treeNodes[i].getParentNode().childDrag === false) {
          curDragNodes = null;
          return false;
        }
      }
      curDragNodes = treeNodes;
      return true;
    }
    function beforeDragOpen(treeId, treeNode) {
      autoExpandNode = treeNode;
      return true;
    }
    function beforeDrop(treeId, treeNodes, targetNode, moveType, isCopy) {
      className = (className === "dark" ? "":"dark");
      return true;
    }
    function onDrag(event, treeId, treeNodes) {
      className = (className === "dark" ? "":"dark");
    }
    function onDrop(event, treeId, treeNodes, targetNode, moveType, isCopy) {

    }

    function onExpand(event, treeId, treeNode) {
      if (treeNode === autoExpandNode) {
        className = (className === "dark" ? "":"dark");
      }
    }

    //由于是数据交互是非阻塞的 手动删除节点
    function beforeRemove(treeId, treeNode) {
      var zTree = $.fn.zTree.getZTreeObj("treeDemo");
      zTree.selectNode(treeNode);
      if(1){
        window.location.reload()
        return false;        
      }
      if(treeNode.level == '0'){
        layer.msg('不能删除根节点');
        return false;
      }
      layer.confirm('确定删除'+treeNode.name+'吗？', function(index) {
        admin.req({
          url: ''
          ,data: {id:treeNode.id}
          ,type: 'post'
          ,done: function(res){
            if(res.code == '0'){
              layer.msg(res.msg);
              return false;
            }
            layer.msg(res.msg);
          }
        });  

        layer.close(index);
        zTree.removeNode(treeNode);
        return true;
      });
      return false;
    }

    function zTreeBeforeEditName(treeId, treeNode) {
      editorDepartment(treeNode.id);
      return !treeNode.isParent;
    }

    //编辑节点名称    
    function beforeRename(treeId, treeNode, newName) {
      newName = $.trim(newName);
      if(loguseradminid!=useradminid){
        window.location.reload()
        return false;        
      }
      if (newName.length == 0) {
        setTimeout(function() {
          var zTree = $.fn.zTree.getZTreeObj("treeDemo");
          zTree.cancelEditName();
          layer.alert('节点名称不能为空');
        }, 0);
        return false;
      }
      var zTree = $.fn.zTree.getZTreeObj("treeDemo");
      zTree.selectNode(treeNode);
      admin.req({
        url: ''
        ,data: {name:newName,id:treeNode.id,proid:'{$info.id}'}
        ,type: 'post'
        ,done: function(res){
          if(res.code == '0'){
            layer.msg(res.msg);
            return false;
          }
          layer.msg(res.msg, {
            offset: '15px'
            ,icon: 1
            ,time: 1000
          }, function(){
            treeNode.name = newName;
            treeObj.updateNode(treeNode);
            window.location.reload();
            return false;
          });
        }
      });   
      return true;
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
                form.render(null, 'operator-update-form-update');
            });
        },
        yes: function (index, layero) {
            //点击确认触发 iframe 内容中的按钮提交
            var submit = $("#operator-update-submit-update");
            submit.click();
        }
      });
    }

    function deleteDepartment(data) {
      let url = '/department.action?command=delete&id='+data.id
      layer.confirm('确定删除吗？', function(index){
        admin.req({
          url: '/department.action?command=delete&id='+data.id
          ,done: function(res){
            if(res.status != '0'){
              layer.msg('删除失败');
              return false;
            }
            klt_department.deleteNode(data.id);
            layer.msg('删除成功', {
              offset: '15px'
              ,icon: 1
              ,time: 1000
            }, function(){
              layui.table.reload('Filter-table-department'); //重载表格
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
          {field: 'name', title: '部门名称'}
          ,{field: 'm_name', title: '部门经理'}
          ,{field: 'remark', title: '备注'}
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
              url: '/department.action?command=delete&id='+data.id
              ,done: function(res){
                if(res.status != '0'){
                  layer.msg('删除失败');
                  return false;
                }
                klt_department.deleteNode(data.id);
                layer.msg('删除成功', {
                  offset: '15px'
                  ,icon: 1
                  ,time: 1000
                }, function(){
                  layui.table.reload('Filter-table-department'); //重载表格
                  layer.close(index); //执行关闭 
                });
              }
            });
        });
      }else if(obj.event === 'edit'){
        admin.popup({
          title: '编辑'
          ,area: ['500px', '480px']
          ,id: 'LAY-popup-user-add'
          ,success: function(layero, index){
            view(this.id).render('department/form', data).done(function(){
              form.render(null, 'Filter-form-department');
              
              //监听提交
              form.on('submit(Filter-form-sumbit-department)', function(data){
                var field = data.field; //获取提交的字段

                //提交 Ajax 成功后，关闭当前弹层并重载表格
                //$.ajax({});
                admin.req({
                  url: '/department.action?command=edit' //实际使用请改成服务端真实接口
                  ,type: 'post'
                  ,data: field            
                  ,done: function(res){
                    if (res.status != '0') {
                      layer.msg('编辑失败');
                      return false;
                    }
                    klt_department.updateNode(field.id,field);
                    //登入成功的提示与跳转
                    layer.msg('编辑成功', {
                      offset: '15px'
                      ,icon: 1
                      ,time: 1000
                      }, function(){
                        layui.table.reload('Filter-table-department'); //重载表格
                        layer.close(index); //执行关闭 
                    });
                  }
                });
                
              });
            });
          }
        });
      }
    });

    var klt_department = function() {};

    //新增节点
    klt_department.addNode = function(pid,node) {
      var treeObj = $.fn.zTree.getZTreeObj("treeDemo");
      var pnode = treeObj.getNodeByParam("id", pid, null);
      childnode = pnode.children;
      if (childnode && childnode.length > 0) {
        node.isParent = true;
        newNode = treeObj.addNodes(pnode,0, node);
      }   
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
    };


    exports('department', klt_department);
  });