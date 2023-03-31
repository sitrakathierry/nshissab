$(document).ready(function(){
    $.ajaxSetup({
        cache: false
      });

      function generatePassword() {
        var length = 8,
            charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+<>?",
            password = "";
        while(password.length < length) {
          var randomChar = charset.charAt(Math.floor(Math.random() * charset.length));
          if(/[a-z]/.test(randomChar) && /[A-Z]/.test(randomChar) && /[0-9]/.test(randomChar) && /[!@#$%^&*()_+<>?]/.test(randomChar)) {
            password += randomChar;
          }
        }
        return password;
      }

    $.getJSON(routes.jsonPath, function(json) {
        // console.log(json); // this will show the info it in firebug console
        var menu = ''
        var i = 1
        var contentCollapse = ''
        json.forEach(element => {
                var route = element.route
                if(route == "none")
                    route = "app_admin"
                var nom = element.nom
                var icone = element.icone
                var attribute = 'href="'+routes[route]+'"'
                var is_submenu = false
                if(element.submenu !== undefined)
                {
                    if(element.submenu.length > 0)
                    {
                        attribute = 'href="#collapse'+i+'" data-toggle="collapse" aria-expanded="true" aria-controls="collapse'+i+'"'
                        is_submenu = true ;
                    }
                }
                
                menu += `
                        <li>
                            <a `+attribute+`><span class="fa `+icone+` mr-2"></span>`+nom+`</a>
                        </li>
                `
                if(is_submenu)
                {
                    
                    var submenu = element.submenu
                    var collapse = `
                    <ul class="list-unstyled collapse ml-3" id="collapse`+i+`" aria-labelledby="heading" data-parent="#accordion">
                    `
                    var j = 1
                    submenu.forEach(elem => {
                        var s_route = elem.route
                        if(s_route == "none")
                            s_route = "app_admin"
                        var s_nom = elem.nom
                        var s_icone = elem.icone
                        var s_attribute = 'href = "'+routes[s_route]+'"'
                        var s_is_submenu = false
                        var contentParent = ''
                        if(elem.submenu !== undefined)
                        {
                            if(elem.submenu.length > 0)
                            {
                                s_attribute = 'href="#subcollapse'+j+'" data-toggle="collapse" aria-expanded="true" aria-controls="subcollapse'+j+'"'
                                s_is_submenu = true ;
                            }
                        }
                        contentParent = `
                            <li>
                                <a `+s_attribute+`><span class="fa `+s_icone+` mr-2"></span>`+s_nom+`</a>
                            </li>
                        `
                        if(s_is_submenu)
                        {
                            var su_collapse = `
                                <ul class="list-unstyled collapse ml-3" id="subcollapse`+j+`" aria-labelledby="subheading`+j+`" data-parent="#collapse`+i+`">
                            `
                            elem.submenu.forEach(elems => {
                                var su_route = elems.route
                                if(su_route == "none")
                                    su_route = "app_admin"
                                var su_nom = elems.nom
                                var su_icone = elems.icone
                                var su_attribute = 'href = "'+routes[su_route]+'"'
                                su_collapse += `
                                    <li>
                                        <a `+su_attribute+`><span class="fa `+su_icone+` mr-2"></span>`+su_nom+`</a>
                                    </li>
                                `
                            })
                            if(j == 1)
                            {
                                menu +=collapse+contentParent+ su_collapse +"</ul>"
                            }
                            else
                            {
                                menu += contentParent+ su_collapse +"</ul>"
                            }
                        }
                        else
                        {
                            if(j == 1)
                            {
                                menu +=collapse+contentParent 
                            }
                            else
                            {
                                menu += contentParent
                            }
                            
                        }
                        j++
                    })
                    menu += "</ul>"
                }
                i++ ;
            })
            $("#accordion").html(menu) ;

        });
    
        function loading()
        {
            var loading = `
            <div class="text-center">
                <img src="`+files.loading+`" class="img img-fluid" alt="">
            </div>
            `
            alertInstance = $.alert({
                title:false,
                content:loading,
                closeIcon: false,
                buttons: false
              });
            
            return alertInstance ; 
        }

        $("#formSociete").submit(function(event){
            event.preventDefault()
            var instance = loading()
            var data = $(this).serialize();
            $.ajax({
                url: routes.admin_saveSociete,
                type:"post",
                data:data,
                dataType:"json",
                success : function(json){
                    instance.close()
                    $.alert({
                        title: 'Message',
                        content: json.message,
                        type: json.type,
                    });
                    if(json.type == "green")
                    {
                        var dataForm = []
                        var elements = data.split("&")
                        for (let index = 0; index < elements.length; index++) {
                            const element = elements[index];
                            dataForm.push(element.split("=")[0]) ;
                        }
                        // const jsonData = JSON.stringify(dataForm);
                        dataForm.forEach(elem => {
                            $("#"+elem).val("")
                        })
                        $.ajax({
                            url : routes.getRandomPass ,
                            type:'post',
                            data:{},
                            dataType: 'json',
                            success : function(resp){
                                $("#password").val(resp.randomPass)
                            }
                        })
                    }
                }
            })
        })
    });
