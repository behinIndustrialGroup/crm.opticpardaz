function show_error(er){
    if(er.status == 403){
        toastr.error("دسترسی ندارید")
    }
    if(er.responseJSON){
        toastr.error(er.responseJSON.message)
    }else if(er.responseText){
        toastr.error(er.responseText)
    }else if(typeof(er) == "string"){
        toastr.error(er);
    }
    else{
        toastr.error("خطا");
    }
    console.log(er);
    hide_loading();
}

function show_message(msg = "انجام شد" ){
    toastr.success(msg);
}

function camaSeprator(className){
    $('.'+ className).on('keyup', function(){
        if($(this).val()){
            $(this).val(parseInt($(this).val().replace(/,/g, '')).toLocaleString())
        }
    })
}

function runCamaSeprator(className){
    $('.'+ className).each(function(){
        if($(this).val()){
            $(this).val(parseInt($(this).val().replace(/,/g, '')).toLocaleString())
        }
    })
}