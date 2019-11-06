
$(function () {

    //内容添加-栏目选择
    $(document).on('change', '#c-channel_id', function () {
        var channel_id = $(this).val();
        //var model_id = $("option:selected", this).attr("model");
        init(channel_id);
    });

})

//显示
function loading_show() {
    $("#extend>table").html("<tr><td colspan='2' style='background:#2C3E50; color:#f00; text-align:center; padding:3px 0px;'>加载中...</td></tr>");
}

//隐藏
function loading_hide() {
    $("#extend>table").html("");
}

//动态字段加载
function init(channel_id) {
    loading_show();
    $.ajax({
        type: 'post',
        url: '/' + GV.APP + '/admin_archives/get_channel_fields',
        data: { "channel_id": channel_id },
        success: function (data) {
            //console.log(data);
            if (data == 'nofields') {//没字段
                loading_hide();
            } else if (data == 'nochannel') {
                alert('请选择下级栏目，并保证该栏目关联了模型！');
                loading_hide();
            } else if (data) {//有字段
                var array = eval(data);
                if (array.length > 0) {
                    //扩展说明
                    $("#extend>table").html("<tr><td colspan='2' style='background:#2C3E50; color:#fff; text-align:center; padding:3px 0px;'>扩展字段</td></tr>");
                    for (i = 0; i < array.length; i++) {
                        //console.log(array[i]);
                        var html = '', uploadtxt = '';
                        var item = array[i];
                        switch (item.type.toLowerCase()) {
                            case "string"://文本
                                html += '<tr>';
                                html += '    <th>' + item.title + '</th>';
                                html += '    <td>';
                                html += '        <input class="form-control" type="text" name="post[' + item.name + ']" id="' + item.name + '" value="" placeholder="请输入' + item.title + '"/>';
                                html += '    </td>';
                                html += '</tr>';
                                break;
                            case "text"://文本框
                                html += '<tr>';
                                html += '    <th>' + item.title + '</th>';
                                html += '    <td>';
                                html += '        <textarea class="form-control" name="post[' + item.name + ']" id="' + item.name + '" style="height: 50px;" placeholder="请填写' + item.title + '"></textarea>';
                                html += '    </td>';
                                html += '</tr>';
                                break;
                            case "number"://数量
                                html += '<tr>';
                                html += '    <th>' + item.title + '</th>';
                                html += '    <td>';
                                html += '        <input class="form-control" type="text" name="post[' + item.name + ']" id="' + item.name + '" value="" placeholder="请输入' + item.title + '"/>';
                                html += '    </td>';
                                html += '</tr>';
                                break;
                            case "radio"://单选按钮
                                html += '<tr>';
                                html += '    <th>' + item.title + '</th>';
                                html += '    <td>';
                                //可以用字符或字符串分割
                                if (item.content != '') {
                                    var content = $.trim(item.content).replace(new RegExp('\n', "gm"), ' ');//jquery实现textarea输入内容换行和空格
                                    var arr = content.split(' ');//例如：1|是 0|否
                                    //开始大遍历
                                    $.each(arr, function (i, obj1) {
                                        var checked = '';
                                        if (i == 0) {
                                            checked = " checked";
                                        }
                                        //console.log('i：' + i);
                                        //开始小遍历
                                        var arr2 = $.trim(obj1).split('|');//例如：1|是
                                        $.each(arr2, function (j, obj2) {
                                            //console.log('j：' + j);
                                            if (j == 0) {
                                                html += '<input type="radio" name="post[' + item.name + ']" value="' + obj2 + '" ' + checked + ' />';
                                            } else {
                                                html += '&nbsp;' + obj2 + '&nbsp;';
                                            }
                                        })
                                    })
                                }
                                html += '        <input type="hidden" name="post[radio_list][]" value="' + item.name + '">';//为了插入处理，而增加的
                                html += '    </td>';
                                html += '</tr>';
                                break;
                            case "checkbox"://复选框按钮
                                html += '<tr>';
                                html += '    <th>' + item.title + '</th>';
                                html += '    <td>';
                                //可以用字符或字符串分割
                                if (item.content != '') {
                                    var content = $.trim(item.content).replace(new RegExp('\n', "gm"), ' ');//jquery实现textarea输入内容换行和空格
                                    var arr = content.split(' ');//例如：1|是 0|否
                                    //开始大遍历
                                    $.each(arr, function (index, j) {
                                        //开始小遍历
                                        var arr2 = $.trim(j).split('|');//例如：1|是
                                        $.each(arr2, function (index, k) {
                                            if (index == 0) {
                                                html += '<input type="checkbox" name="post[' + item.name + '][]" value="' + k + '" />';
                                            } else {
                                                html += '&nbsp;' + k + '&nbsp;';
                                            }
                                        })
                                    })
                                }
                                html += '        <input type="hidden" name="post[checkbox_list][]" value="' + item.name + '">';//为了插入处理，而增加的
                                html += '    </td>';
                                html += '</tr>';
                                break;
                            case "date"://日期
                                html += '<tr>';
                                html += '    <th>' + item.title + '</th>';
                                html += '    <td>';
                                html += '        <input class="form-control js-bootstrap-' + item.name + '" type="text" name="post[' + item.name + ']" value="" />';
                                html += '<script>';
                                html += '   $(function () {';
                                html += '       var bootstrapDateInput_' + item.name + ' = $("input.js-bootstrap-' + item.name + '");';
                                html += '       if (bootstrapDateInput_' + item.name + '.length) {';
                                html += '           Wind.css(\'bootstrapDatetimePicker\');';
                                html += '           Wind.use(\'bootstrapDatetimePicker\', function () {';
                                html += '               bootstrapDateInput_' + item.name + '.datetimepicker({';
                                html += '                   language: \'zh-CN\',';
                                html += '                   format: \'yyyy-mm-dd\',';
                                html += '                   minView: \'month\',';
                                html += '                   todayBtn: 1,';
                                html += '                   autoclose: true';
                                html += '               });';
                                html += '           });';
                                html += '       }';
                                html += '   })';
                                html += '<\/script>';
                                html += '    </td>';
                                html += '</tr>';
                                break;
                            case "time"://时间
                                html += '<tr>';
                                html += '    <th>' + item.title + '</th>';
                                html += '    <td>';
                                html += '        <input class="form-control js-bootstrap-' + item.name + '" type="text" name="post[' + item.name + ']" value="" />';
                                html += '<script>';
                                html += '   $(function () {';
                                html += '       var bootstrapDateInput_' + item.name + ' = $("input.js-bootstrap-' + item.name + '");';
                                html += '       if (bootstrapDateInput_' + item.name + '.length) {';
                                html += '           Wind.css(\'bootstrapDatetimePicker\');';
                                html += '           Wind.use(\'bootstrapDatetimePicker\', function () {';
                                html += '               bootstrapDateInput_' + item.name + '.datetimepicker({';
                                html += '                   language: \'zh-CN\',';
                                html += '                   format: \'hh:ii\',';
                                html += '                   autoclose: true';
                                html += '               });';
                                html += '           });';
                                html += '       }';
                                html += '   })';
                                html += '<\/script>';
                                html += '    </td>';
                                html += '</tr>';
                                break;
                            case "datetime"://日期时间
                                html += '<tr>';
                                html += '    <th>' + item.title + '</th>';
                                html += '    <td>';
                                html += '        <input class="form-control js-bootstrap-' + item.name + '" type="text" name="post[' + item.name + ']" value="" />';
                                html += '<script>';
                                html += '   $(function () {';
                                html += '       var bootstrapDateInput_' + item.name + ' = $("input.js-bootstrap-' + item.name + '");';
                                html += '       if (bootstrapDateInput_' + item.name + '.length) {';
                                html += '           Wind.css(\'bootstrapDatetimePicker\');';
                                html += '           Wind.use(\'bootstrapDatetimePicker\', function () {';
                                html += '               bootstrapDateInput_' + item.name + '.datetimepicker({';
                                html += '                   language: \'zh-CN\',';
                                html += '                   format: \'yyyy-mm-dd hh:ii\',';
                                html += '                   todayBtn: 1,';
                                html += '                   autoclose: true';
                                html += '               });';
                                html += '           });';
                                html += '       }';
                                html += '   })';
                                html += '<\/script>';
                                html += '    </td>';
                                html += '</tr>';
                                break;
                            case "editor"://编辑器
                                html += '<tr>';
                                html += '    <th style="width:12%;">' + item.title + '<span class="form-required">*</span></th>';
                                html += '    <td>';
                                html += '        <script type="text/plain" id="' + item.name + '" name="post[' + item.name + ']"><\/script>';
                                html += '    </td>';
                                html += '<script>';
                                html += '   $(function () {'
                                html += '       editor_' + item.name + ' = new baidu.editor.ui.Editor();';
                                html += '       editor_' + item.name + '.render(\'' + item.name + '\');';
                                html += '       try {';
                                html += '           editor_' + item.name + '.sync();';
                                html += '       } catch (err) {';
                                html += '       }';
                                html += '   })';
                                html += '<\/script>';
                                html += '</tr>';
                                break;
                            case "image"://单图片
                                html += '<tr>';
                                html += '    <th>' + item.title + '</th>';
                                html += '    <td>';
                                html += '        <input type="hidden" name="post[' + item.name + ']" id="' + item.name + '" value="">';
                                html += '        <a href="javascript:uploadOneImage(\'图片上传\',\'#' + item.name + '\');">';
                                html += '            <img src="/themes/admin_simpleboot3/public/assets/images/default-thumbnail.png" id="' + item.name + '-preview" width="60" style="cursor: pointer" />';
                                html += '        </a>';
                                html += '        <input type="button" class="btn btn-sm btn-cancel-' + item.name + '" value="取消图片">';
                                html += '    </td>';
                                html += '<script>';
                                html += '   $(function () {';
                                html += '      $(\'.btn-cancel-' + item.name + '\').click(function () {';
                                html += '          $(\'#' + item.name + '-preview\').attr(\'src\', \'/themes/admin_simpleboot3/public/assets/images/default-thumbnail.png\');';
                                html += '          $(\'#' + item.name + '\').val(\'\');';
                                html += '      });';
                                html += '   })';
                                html += '<\/script>';
                                html += '</tr>';
                                break;
                            case "images"://多图片(相册)
                                html += '<tr>';
                                html += '    <th>' + item.title + '</th>';
                                html += '    <td>';
                                html += '        <ul id="' + item.name + '" class="pic-list list-unstyled form-inline"></ul>';
                                html += '        <a href="javascript:uploadMultiImage(\'图片上传\',\'#' + item.name + '\',\'' + item.name + '-item-tpl\');" class="btn btn-default btn-sm">选择图片</a>';
                                html += '        <input type="hidden" name="post[images_files][]" value="' + item.name + '">';//为了插入处理，而增加的
                                html += '    </td>';
                                html += '</tr>';

                                uploadtxt += '<script type="text/html" id="' + item.name + '-item-tpl">';
                                uploadtxt += '    <li id="saved-image{id}">';
                                uploadtxt += '        <input id="photo-{id}" type="hidden" name="post[' + item.name + '_urls][]" value="{filepath}">';
                                uploadtxt += '        <input class="form-control" id="photo-{id}-name" type="text" name="post[' + item.name + '_names][]" value="{name}" style="width: 200px;" title="图片名称">';
                                uploadtxt += '        <img id="photo-{id}-preview" src="{url}" style="height:36px;width: 36px;" onclick="imagePreviewDialog(this.src);">';
                                uploadtxt += '        <a href="javascript:uploadOneImage(\'图片上传\',\'#photo-{id}\');">替换</a>';
                                uploadtxt += '        <a href="javascript:(function(){$(\'#saved-image{id}\').remove();})();">移除</a>';
                                uploadtxt += '    </li>';
                                uploadtxt += '<\/script>';
                                break;
                            case "file"://单文件
                                html += '<tr>';
                                html += '    <th>' + item.title + '</th>';
                                html += '    <td class="form-inline">';
                                html += '            <input id="file-' + item.name + '" class="form-control" type="text" name="post[' + item.name + ']" value="" placeholder="请上传文件" style="width: 200px;">';
                                html += '            <a href="javascript:uploadOne(\'文件上传\',\'#file-' + item.name + '\',\'file\');">上传</a>';
                                html += '    </td>';
                                html += '</tr>';
                                break;
                            case "files"://多文件
                                html += '<tr>';
                                html += '    <th>' + item.title + '</th>';
                                html += '    <td>';
                                html += '        <ul id="' + item.name + '" class="pic-list list-unstyled form-inline"></ul>';
                                html += '        <a href="javascript:uploadMultiFile(\'文件上传\',\'#' + item.name + '\',\'' + item.name + '-item-tpl\',\'file\');" class="btn btn-default btn-sm">选择文件</a>';
                                html += '        <input type="hidden" name="post[images_files][]" value="' + item.name + '">';//为了插入处理，而增加的
                                html += '    </td>';
                                html += '</tr>';

                                uploadtxt += '<script type="text/html" id="' + item.name + '-item-tpl">';
                                uploadtxt += '    <li id="saved-file{id}">';
                                uploadtxt += '        <input id="file-{id}" type="hidden" name="post[' + item.name + '_urls][]" value="{filepath}">';
                                uploadtxt += '        <input class="form-control" id="file-{id}-name" type="text" name="post[' + item.name + '_names][]" value="{name}" style="width: 200px;" title="文件名称">';
                                uploadtxt += '        <a id="file-{id}-preview" href="{preview_url}" target="_blank">下载</a>';
                                uploadtxt += '        <a href="javascript:uploadOne(\'文件上传\',\'#file-{id}\',\'file\');">替换</a>';
                                uploadtxt += '        <a href="javascript:(function(){$(\'#saved-file{id}\').remove();})();">移除</a>';
                                uploadtxt += '    </li>';
                                uploadtxt += '<\/script>';
                                break;
                            default:
                                break;
                        }
                        $("#extend>table").append(html);
                        $("#extend_upload").append(uploadtxt);
                    }
                }
            } else {
                loading_hide();
            }
        }
    })
}
