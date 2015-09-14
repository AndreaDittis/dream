;
var posts_add_ops = {
    init: function () {
        this.initPlugins();
        this.eventBind();
    },
    eventBind: function () {
        var that = this;
        $("#post_add_edit .save").click(function(){
            if(!that.inputCheck()){
                return false;
            }
            that.dataSubmit();
            return false;
        });
    },
    inputCheck:function(){
        var title = $.trim( $("#post_add_edit input[name=title]").val() );
        var content  = $.trim( $("#post_add_edit .summernote").code() );
        var tags = $.trim( $("#post_add_edit input[name=tags]").val() );
        var type = $.trim($("#type").val());
        if(title.length <= 0){
            alert("请输入博文标题!!");
            $("#post_add_edit input[name=title]").focus();
            return false;
        }

        if(content.length <= 10){
            alert("请输入更多点博文内容!!");
            $("#post_add_edit .summernote").focus();
            return false;
        }

        if(tags.length <= 0){
            alert("请输入博文tags!!");
            $("#post_add_edit input[name=tags]").focus();
            return false;
        }

        if( type == undefined || parseInt(type) <= 0){
            alert("请选择类型!!");
            return false;
        }

        return true;
    },
    dataSubmit:function(){
        var post_id = $.trim($("#post_add_edit input[name=post_id]").val() );
        var title = $.trim( $("#post_add_edit input[name=title]").val() );
        var content  = $.trim( $("#post_add_edit .summernote").code() );
        var tags = $.trim( $("#post_add_edit input[name=tags]").val() );
        var type = $.trim($("#type").val());
        data = {
            id:post_id,
            title:title,
            content:content,
            tags:tags,
            type:type
        };
        $.ajax({
            url:'/posts/add',
            data:data,
            type:'POST',
            dataType:'json',
            success:function(res){
                alert(res.msg);
                if(res.code == 200){
                    window.location.href = "/posts/index";
                }
            }
        });
    },
    initPlugins:function(){
        $("#post_add_edit input[name=tags]").tagsInput({
            width:'auto',
            height:30,
            onAddTag:function(tag){
                //console.log('增加了'+tag)
            },
            onRemoveTag:function(tag){
                //console.log('删除了'+tag)
            }
        });
    }
};

$(document).ready(function () {
    posts_add_ops.init();
});