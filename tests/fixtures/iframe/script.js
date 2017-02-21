(function(window) {
  window.moodleLocalSigninIframeReceiver = function(options) {
    this.defaults = {
      iframeSelector: '#login-frame'
    };

    options = Object.assign({}, this.defaults, options);
    const loginFrame = document.querySelector(options.iframeSelector);

    window.addEventListener('message', function(e) {
      const payload = JSON.parse(e.data);
      switch (payload.type) {
        case 'moodleLocalSignin/resize':
          console.debug('Login frame receiver received resize', payload.data.width, 'x', payload.data.height, e);
          loginFrame.width = payload.data.width;
          loginFrame.height = payload.data.height;

          loginFrame.style.width = payload.data.width;
          loginFrame.style.height = payload.data.height;

          break;

        default:
          console.warn('Login frame receiver received message of unknown type', e);
      }
    }, false);
    console.info('Login frame receiver configured', options);
  }
})(window);
