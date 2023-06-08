$(document).ready(function(){
    var appBase = new AppBase() ;
    var agenda_editor = new LineEditor(".agenda_editor") ;
    var instance = new Loading(files.loading)
    $("#agd_date").datepicker() ; 

    $("#formAgenda").submit(function(){
        var self = $(this);
        $(".agenda_editor").val(agenda_editor.getEditorText('.agenda_editor'))
        $.confirm({
          title: "Confirmation",
          content: "Etes-vous sûre de vouloir enregistrer ?",
          type: "blue",
          theme: "modern",
          buttons: {
            btn1: {
              text: "Non",
              action: function () {
                $("input, select").val("")
                agenda_editor.setEditorText("")
              },
            },
            btn2: {
              text: "Oui",
              btnClass: "btn-blue",
              keys: ["enter", "shift"],
              action: function () {
                var data = self.serialize();
                var realinstance = instance.loading();
                $.ajax({
                  url: routes.agd_activites_save,
                  type: "post",
                  data: data,
                  dataType: "json",
                  success: function (json) {
                    realinstance.close();
                    $.alert({
                      title: "Message",
                      content: json.message,
                      type: json.type,
                      buttons: {
                        OK: function () {
                          if (json.type == "green") {
                            var elements = data.split("&");
                            elements.forEach((elem) => {
                              $("#" + elem.split("=")[0]).val("");
                            });
                            agenda_editor.setEditorText("")
                            location.reload();
                          }
                        },
                      },
                    });
                  },
                  error: function (resp) {
                    realinstance.close();
                    $.alert(JSON.stringify(resp));
                  },
                });
              },
            },
          },
        });
          return false;
      });

    $("#agd_type").change(function(){
        var selectedOption = $(this).find("option:selected") ;
        var libelle = selectedOption.data('libelle') ;

        $("#agd_nom").val(libelle) ;
        $(".agdDesignation").text(libelle.toUpperCase()) ;
    }) ;

    $.getJSON($("#calendarPath").val(), function(json) {
      $("#monCalendrier").zabuto_calendar({
        classname: 'table table-bordered lightgrey-weekends clickable',
        // header_format: '[year] // [month]',
        week_starts: 'sunday',
        show_days: true,
        today_markup: '<span class="font-weight-bold text-info">[day]</span>',
        navigation_markup: {
            prev: '<i class="fas fa-chevron-circle-left"></i>',
            next: '<i class="fas fa-chevron-circle-right"></i>'
          },
          language: 'fr',
          events : json
      });

    });
    
   
    $("#monCalendrier").on('zabuto:calendar:day', function (e) {
        var semaines = [
            "Dimanche",
            "Lundi",
            "Mardi",
            "Mercredi",
            "Jeudi",
            "Vendredi",
            "Samedi"
        ]
        var captionToday = e.today ? "Aujourd'hui, ": "" ;
        var captionDate = captionToday+semaines[e.date.getDay()]+" "+e.date.getDate()+" "+appBase.getMonthName(e.date.getMonth())+" "+e.date.getFullYear() ;
        $("#dispDate").text(captionDate)
        $("#identityDate").text(captionDate)
        // console.log('zabuto:calendar:day' + ' date=' + e.date.toDateString() + ' value=' + e.value + ' today=' + e.today);
        // console.log(e.currentTarget.innerHTML.)
        $(e.currentTarget).find('.zabuto-calendar__day').each(function(){
            if(!($(this).is(e.element)))
            {
                if(!$(this).hasClass('zabuto-calendar__event'))
                {
                    if($(this).find('span').hasClass("badge text-white bg-warning"))
                    {
                        $(this).html($(this).find('span').text())
                    }
                }
            }
            // console.log($(this).is(e.element))
        })
        var dayToday = $(e.currentTarget).find('.zabuto-calendar__day--today')
        if(dayToday.find('span').hasClass("badge text-white bg-warning"))
        {
            dayToday.find('span').removeClass('badge text-white bg-warning')
            dayToday.find('span').addClass('font-weight-bold text-info')
        }

        if(!$(e.element).hasClass('zabuto-calendar__event'))
        {
            $(e.element).html('<span class="badge text-white bg-warning">'+e.date.getDate()+'</span>')
        }
        
        // EVENEMENT affiche Date
        var formData = new FormData() ;
        var mois = (e.date.getMonth()+1).toString().padStart(2, '0');
        var jour = (e.date.getDate()).toString().padStart(2, '0');
        var date = e.date.getFullYear()+"-"+mois+"-"+jour
        formData.append("date",date)
        var realinstance = instance.loading()
        $.ajax({
            url: routes.agd_activites_details_date,
            type:'post',
            cache: false,
            data:formData,
            dataType: 'html',
            processData: false,
            contentType: false,
            success: function(resp){
              realinstance.close()
              $.confirm({
                title: '<h5 class="text-black font-weight-bold text-center text-uppercase" >'+captionDate+'</h5>',
                content:resp,
                columnClass: 'agdDisplayDate',
                type:"black",
                theme:"modern",
                buttons:{
                  btn1:{
                      text: 'Fermer',
                      action: function(){}
                  }
                }
              })
            },
            error: function(resp){
                realinstance.close()
                $.alert(JSON.stringify(resp)) ;
            }
        })
        // console.log(e.element)
    });
})