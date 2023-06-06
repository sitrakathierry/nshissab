$(document).ready(function(){
    var appBase = new AppBase() ;
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
        events : [
            {
                "date": "2023-06-07",
                "markup": '<div class="bg-primary badge text-white">[day]</div>'
            },
            {
                "date": "2023-06-05",
                "markup": '<div class="bg-dark badge text-white">[day]</div>'
            },
            {
                "date": "2023-06-01",
                "markup": '<div class="bg-danger badge text-white">[day]</div>'
            },
            {
                "date": "2023-06-16",
                "markup": '<div class="bg-primary badge text-white">[day]</div>'
            }, 
            {
                "date": "2023-06-12",
                "markup": '<div class="bg-purple badge text-white">[day]</div>'
            },
            {
                "date": "2023-06-02",
                "markup": '<div class="bg-success badge text-white">[day]</div>'
            },
        ]
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
        
        // console.log(e.element)
    });
})