<html>

<head>
  <meta charset="utf-8" />
  <!-- Firebase -->
  <script src="https://www.gstatic.com/firebasejs/5.5.4/firebase.js"></script>

  <!-- CodeMirror -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.17.0/codemirror.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.17.0/codemirror.css" />
  <link rel="stylesheet" href="style.css" />
  <!-- Firepad -->
  <link rel="stylesheet" href="https://cdn.firebase.com/libs/firepad/1.4.0/firepad.css" />
  <script src="https://cdn.firebase.com/libs/firepad/1.4.0/firepad.min.js"></script>

  <style>
    html { height: 100%; }
    body { margin: 0; height: 100%; position: relative; }
      /* Height / width / positioning can be customized for your use case.
         For demo purposes, we make firepad fill the entire browser. */
    #firepad-container {
      width: 100%;
      height: 100%;
    }
  </style>
  <script type="text/javascript">
    function dataURLtoBlob(dataurl) {
      var arr = dataurl.split(','), mime = arr[0].match(/:(.*?);/)[1],
          bstr = atob(arr[1]), n = bstr.length, u8arr = new Uint8Array(n);
      while(n--){
          u8arr[n] = bstr.charCodeAt(n);
      }
      return new Blob([u8arr], {type:mime});
    }
  </script>
</head>

<body onload="init()">
  <div id="firepad-container"></div>

  <script>

    
    function init() {
      //// Initialize Firebase.
      //// TODO: replace with your Firebase project configuration.
      var config = {
        apiKey: '<API_KEY>',
        authDomain: "firepad-tests.firebaseapp.com",
        databaseURL: "https://firepad-tests.firebaseio.com"
      };
      firebase.initializeApp(config);
      //// Get Firebase Database reference.
      var firepadRef = getExampleRef();
      //// Create CodeMirror (with lineWrapping on).
      var codeMirror = CodeMirror(document.getElementById('firepad-container'), { lineWrapping: true });
      //// Create Firepad (with rich text  firepad.insertEntity and shortcuts enabled).
      var firepad = Firepad.fromCodeMirror(firepadRef, codeMirror,
          { richTextToolbar: true, richTextShortcuts: true });
      //// Initialize contents.
      firepad.on('ready', function() {
        var toolbar_warp = document.getElementsByClassName("firepad-toolbar-wrapper");
        var upload_btn = '<div class="firepad-btn-group"><a class="firepad-btn" id="upload-image"><span class="firepad-tb-upload-image">Upload</span></a></div>';
        var div = document.createElement('div');
        div.innerHTML = upload_btn.trim();
        var upload_btn_dom = div.firstChild;
        toolbar_warp[0].appendChild(upload_btn_dom);
        document.getElementById("upload-image").addEventListener("click", uploadImages);
        function uploadImages(e) {
          var firepad_html = firepad.getHtml();
          var firedom = document.createElement('div');
          firedom.innerHTML = firepad_html.trim();
          var firepad_dom = firedom.firstChild;
          var imgs = firepad_dom.getElementsByTagName('img');
          for (var i = 0; i < imgs.length; i++) {
            var img = imgs[i];
            var dataurl = img.currentSrc;
            var blob = dataURLtoBlob(dataurl);
            
            var filename = Math.floor((1 + Math.random()) * 0x10000).toString(16)+".png";

            var file = new File([blob], filename);
            console.log(file);
            var xhr = new XMLHttpRequest();
            xhr.onload = function() {
                if (xhr.status == 200) {
                    console.log("uploaded.");
                } else {
                    console.log("oops, something wrong.")
                }

            };
            xhr.onerror = function() {
                console.log("cannot connect to server.");
            };
            xhr.open("POST", "upload.php?filename="+filename, true);
            xhr.setRequestHeader("Content-Type", file.type);
            xhr.send(file);

          }

        }
        document.getElementById("firepad-container").addEventListener("paste", pasteHandler);
        function pasteHandler(e) {

          
          var items = e.clipboardData.items;
          for (var i = 0 ; i < items.length ; i++) {
              var item = items[i];
              if (item.type.indexOf("image") >=0) {
                  file = item.getAsFile()
                  var reader = new FileReader();
                  reader.addEventListener("load", function () {
                    firepad.insertEntity('img', { 
                      'src' : reader.result,
                    });
                  }, false);

                  if (file) {
                    reader.readAsDataURL(file);
                  }
                  // console.log(reader.readAsDataURL(item.getAsFile()));
                  // var xhr = new XMLHttpRequest();
                  // xhr.onload = function() {
                  //     if (xhr.status == 200) {
                  //         console.log("uploaded.");
                  //         firepad.insertEntity('img', { 
                  //           'src' : '/uploads/'+filename,
                  //         });
                  //     } else {
                  //         console.log("oops, something wrong.")
                  //     }
                  // };
                  // xhr.onerror = function() {
                  //     console.log("cannot connect to server.");
                  // };
                  // xhr.open("POST", "upload.php?filename="+filename, true);
                  // xhr.setRequestHeader("Content-Type", item.getAsFile().type);
                  // xhr.send(item.getAsFile());
              } else {
                  console.log("Ignoring non-image.");
              }
          }
      }
        
        
      });
      // An example of a complex custom entity.
      firepad.registerEntity('checkbox', {
        render: function (info, entityHandler) {
          var inputElement = document.createElement('input');
          inputElement.setAttribute('type', 'checkbox');
          if(info.checked) {
            inputElement.checked = 'checked';
          }
          inputElement.addEventListener('click', function () {
            entityHandler.replace({checked:this.checked});
          });
          return inputElement;
        }.bind(this),
        fromElement: function (element) {
          var info = {};
          if(element.hasAttribute('checked')) {
            info.checked = true;
          }
          return info;
        },
        update: function (info, element) {
          if (info.checked) {
            element.checked = 'checked';
          } else {
            element.checked = null;
          }
        },
        export: function (info) {
          var inputElement = document.createElement('checkbox');
          if(info.checked) {
            inputElement.setAttribute('checked', true);
          }
          return inputElement;
        }
      });
    }
    // Helper to get hash from end of URL or generate a random one.
    function getExampleRef() {
      var ref = firebase.database().ref();
      var hash = window.location.hash.replace(/#/g, '');
      if (hash) {
        ref = ref.child(hash);
      } else {
        ref = ref.push(); // generate unique location.
        window.location = window.location + '#' + ref.key; // add it as a hash to the URL.
      }
      if (typeof console !== 'undefined') {
        console.log('Firebase data: ', ref.toString());
      }
      return ref;
    }
  </script>
</body>
</html>

 