class CustomImage
{
    constructor(base64)
    {
        this.base64 = base64 ;
    }

    limitBase64ImageSize(maxSizeInBytes) {
        var maxQuality = 1.0;
        var img = new Image();
        img.src = this.base64;
        var canvas = document.createElement('canvas');
        var ctx = canvas.getContext('2d');
        var deferred = new $.Deferred();
      
        img.onload = function() {
          canvas.width = img.width;
          canvas.height = img.height;
          ctx.drawImage(img, 0, 0);
          var quality = maxQuality;
          var base64data = canvas.toDataURL('image/jpeg', quality);
          var bytes = window.atob(base64data.split(',')[1]);
          while (bytes.length > maxSizeInBytes && quality > 0) {
            quality -= 0.1;
            base64data = canvas.toDataURL('image/jpeg', quality);
            bytes = window.atob(base64data.split(',')[1]);
          }
          deferred.resolve(base64data);
      };
        
        return deferred.promise();
    }

    resizeBase64Img(width, height) 
    {
        var canvas = document.createElement('canvas');
        var ctx = canvas.getContext('2d');
        var img = new Image();
        img.src = this.base64;
        canvas.width = width;
        canvas.height = height;
        ctx.drawImage(img, 0, 0, width, height);
        return canvas.toDataURL();
    }

    encodeToBase64(file) {
        return new Promise((resolve, reject) => {
          const reader = new FileReader();
          reader.readAsDataURL(file);
          reader.onload = () => {
            resolve(reader.result.split(',')[1]);
          };
          reader.onerror = reject;
        });
    }
}
