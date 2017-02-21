define(function() {
  return {
    init: function(options) {
      this.defaults = {
        rootNodeSelector: 'body'
      };

      function postParent(type, data) {
        window.parent.postMessage(JSON.stringify({
          type: 'moodleLocalSignin/' + type,
          data: data
        }), '*');
      }

      function postParentSize(width, height) {
        postParent('resize', {
          width: width,
          height: height
        });
      }

      function getDimensions(node) {
        const style = getComputedStyle(node);
        return {
          width: node.offsetWidth + parseInt(style.marginLeft) + parseInt(style.marginRight) + parseInt(style.paddingLeft) + parseInt(style.paddingRight),
          height: node.offsetHeight + parseInt(style.marginTop) + parseInt(style.marginBottom) + parseInt(style.paddingTop) + parseInt(style.paddingBottom)
        };
      }

      options = Object.assign({}, this.defaults, options);
      const rootNode = document.querySelector(options.rootNodeSelector);

      rootNode.addEventListener('resize', function(e) {
        console.log('Login frame sender resize', e);
        postParentSize(e.offsetWidth, e.offsetHeight)
      });

      (function() {
        const dimensions = getDimensions(rootNode);
        postParentSize(dimensions.width, dimensions.height);
      })();
    }
  };
});
