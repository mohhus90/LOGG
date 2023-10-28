$(document).ready(function(){

    $(document).on('click','.are_you_sure',function(e){
        var res=confirm("هل انت متأكد");
        if(!res){
            return false;
        }
    })
})