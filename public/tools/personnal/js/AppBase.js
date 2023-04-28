class AppBase
{
    constructor()
    {
        
    }

    checkData(elements)
      {
        var message = ""
        var type = ""
        var allow = true ;
        for (let i = 0; i < elements.length; i++) {
            const element = elements[i];
            if($(element.selector).val() == "")
            {
                message = element.title+" vide"
                type = element.type
                allow = false
                break ;
            }
        }

        if(!allow)
        {
            $.alert({
                title: "Message",
                content: message,
                type:type
            })
        }

        return allow
      }

    searchElement(resultContent, url, tableSearch, nbColumn)
    {
        
    }
}