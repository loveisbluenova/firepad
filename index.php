<!doctype html>
<!-- See http://www.firepad.io/docs/ for detailed embedding docs. -->
<html>
<head>
  <meta charset="utf-8" />
  <!-- Firebase -->
  <script src="https://www.gstatic.com/firebasejs/4.0.0/firebase.js"></script>

  <!-- Quill -->
  <link href="https://cdn.quilljs.com/1.2.4/quill.snow.css" rel="stylesheet">
  <script src="https://cdn.quilljs.com/1.2.4/quill.js"></script>

  <!-- Firepad -->
  <script src="../lib/utils.js"></script>
  <script src="../lib/client.js"></script>
  <script src="../lib/wrapped-operation.js"></script>
  <script src="../lib/cursor.js"></script>
  <script src="../lib/firebase-adapter.js"></script>
  <script src="../lib/undo-manager.js"></script>
  <script src="../lib/rich-text-quill-adapter.js"></script>
  <script src="../lib/editor-client.js"></script>
  <script src="../lib/firepad.js"></script>
</head>

<body onload="init()">
  <!-- Create the container -->
  <div id="toolbar"></div>
  <div id="editor-container"></div>

  <script>
    function init() {
      //// Initialize Firebase.
      //// TODO: replace with your Firebase project configuration.
      var config = {
        apiKey: "AIzaSyC_JdByNm-E1CAJUkePsr-YJZl7W77oL3g",
        authDomain: "firepad-tests.firebaseapp.com",
        databaseURL: "https://firepad-tests.firebaseio.com"
      };
      firebase.initializeApp(config);
      //// Get Firebase Database reference.
      var firepadRef = getExampleRef();
      //// Create Quill.
      var toolbarOptions = [
        ['bold', 'italic', 'underline', 'strike'],
        [{'color': []}, {'background': []}],          // dropdown with defaults from theme
        [{'align': []}],
        ['link', 'image'],
        [{'font': []}],
        ['clean']                                         // remove formatting button
      ];
      var editor = new Quill('#editor-container', {
        modules: {
          toolbar: toolbarOptions
        },
        theme: 'snow'  // or 'bubble'
      });
      //// Create Firepad.
      var pad = firepad.Firepad.fromQuill(firepadRef, editor, null);
      //// Initialize contents.
      pad.on('ready', function () {
        if (pad.isHistoryEmpty()) {
//          pad.setText('Compose an epic...');
        }
      });
    }
    // Helper to get hash from end of URL or generate a random one.
    function getExampleRef() {
      var ref = firebase.database().ref('richText');
      var hash = window.location.hash.replace(/#/g, '');
      if (hash) {
        ref = ref.child(hash);
      } else {
        ref = ref.child('default');
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