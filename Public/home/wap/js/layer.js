function layermy(con,fn = '',cancel = false){
    this.con = con;
    this.cancel = cancel == true ? '<div style="width: 32%;background-color: #a0a0a0;text-align: center;margin: 0 auto;margin-top: 10px;margin-bottom: 20px;height: 36px;line-height: 36px;border-radius: 5px;color: white;" id="cancelmy">取消</div>':'';
    this.str = ' <div id="layermy" class="layer-wrap" style="z-index:999999;display:none;position:fixed;left:0;top: 0;background-color: rgba(0,0,0,.5);width: 100%;height: 100%;margin: 0 auto;">'+
                    '<div class="layer" style="width: 80%;'+
                    'margin-left: 10%;'+
                    'background-color: white;'+
                    'border-radius: 10px;'+
                    'margin-top: 40%;'+
                    'overflow: hidden;">'+
                    '<p style="text-align: center;'+
                    'padding: 5px 0;'+
                    'font-size: 21px;'+
                    'color: #03A9F4;margin-top: 10px;">温馨提示</p>'+
                    '<p style="text-align: center;'+
                    'font-size: 18px;'+
                    'padding: 10px 0;'+
                    'line-height: 20px;color: black;"> '+this.con+'</p>'+
                    '<div style="display:flex;justify-content: space-between;align-items: center">'+
                    '<div style="width: 32%;'+
                    'background-color: #2196F3;'+
                    'text-align: center;'+
                    'margin: 0 auto;'+
                    'margin-top: 10px;'+
                    'margin-bottom: 20px;'+
                    'height: 36px;'+
                    'line-height: 36px;'+
                    'border-radius: 5px;'+
                    'color: white;" id="closemy">确定</div>'+
                    this.cancel +
                    '</div>'
                    '</div>'+
                '</div>';
    this.append = function(){
        $('body').append(this.str);
        $('#layermy').show();
        let close = $('#closemy');
        close.click(()=>{
            $('#layermy').remove();
            if( typeof fn == 'function'){
                fn('1');
            }
        })
        if(cancel){
            let cancel = $('#cancelmy');
            cancel.click(()=>{
                $('#layermy').remove();
                if( typeof fn == 'function'){
                    fn('2');
                }
            })
        }
    }
}