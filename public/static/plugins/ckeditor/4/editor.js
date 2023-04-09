class editorJs{}
editorJs.language = 'zh-cn';
editorJs.uploadUrl = '/merchant/upload/editor';
editorJs.create = function(id,callback)
{
    ClassicEditor.create(document.querySelector(id), {
        toolbar: editorJs.toolbar,
        language: editorJs.language,
        table: {
            contentToolbar: [
                'tableColumn',
                'tableRow',
                'mergeTableCells',
                'tableCellProperties',
                'tableProperties'
            ]
        },
        licenseKey: '',
        simpleUpload:{
            uploadUrl:editorJs.uploadUrl,
            headers: {
                token:App.getCache("token")
            }
        },
        mediaEmbed:{
            extraProviders:[
                {name:'allow-all',url:/^.+/,html:match => `<video controls='controls' width='100%'><source src='${match}' /></video>`}
            ],
            previewsInData:true
        }
    }).then( editor => {
        if(callback){
            callback(editor);
        }else{
            window.editor = editor;
        }
    }).catch( error => {
        //console.error( error );
    } );
}
//https://ckeditor.com/latest/samples/toolbarconfigurator/index.html#basic
editorJs.toolbar = {
    items: [
        'sourceEditing',
        '|',
        'heading',
        'fontBackgroundColor',
        'fontColor',
        'fontFamily',
        'fontSize',
        'bold',
        '|',
        'link',
        'bulletedList',
        'numberedList',
        'blockQuote',
        'insertTable',
        '|',
        'imageInsert',
        'mediaEmbed',
        'undo',
        'redo'
    ]
};