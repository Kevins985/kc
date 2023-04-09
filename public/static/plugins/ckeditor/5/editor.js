class editorJs{}
editorJs.language = 'zh-cn';
editorJs.uploadUrl = '/merchant/upload/editor';
editorJs.create = function(id,callback)
{
    ClassicEditor.create(document.querySelector(id), {
        language: editorJs.language,
        licenseKey: '',
        ckfinder:{
            uploadUrl: editorJs.uploadUrl
        },
        mediaEmbed:{
            extraProviders:[
                {name:'allow-all',url:/^.+/,html:match => `<video controls='controls' width='100%'><source src='${match}' /></video>`}
            ],
            previewsInData:true
        }
    }).then( editor => {
        editor.plugins.get('FileRepository').createUploadAdapter = (loader)=>{
            return new UploadAdapter(loader);
        };
        if(callback){
            callback(editor);
        }else{
            window.editor = editor;
        }
    }).catch( error => {
        //console.error( error );
    });
}
//重点代码 适配器
class UploadAdapter {
    constructor(loader) {
        this.loader = loader;
    }
    upload() {
        return new Promise((resolve, reject) => {
            const data = new FormData();
            let file = [];
            this.loader.file.then(res=>{
                data.append('upload', res);
                data.append('type','ckeditor5');
                App.loading();
                $.ajax({
                    url: editorJs.uploadUrl,            //后端的上传接口
                    type: 'POST',
                    data: data,
                    dataType: 'json',
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        App.loading("close");
                        if(response.status){
                            resolve({
                                default: response.data.file_url
                            });
                        } else {
                            reject(response.msg);
                        }
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        App.loading('close');
                    }
                });
            })
        });
    }
    abort() {
    }
}