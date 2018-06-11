var express = require('express');
var router = express.Router();

/* GET form. */
/* GET home page. */
router.get('/', function(req, res, next) {
  res.render('index', {
    title: 'Home',
    navitems: [
      {link: '/', content: "Home"},
      {link: '/registerForm', content: "registerForm"}
    ]
  });
});

/* GET registerForm page */
router.get('/registerForm', function(req, res, next) {
  res.render('registerForm', {
    title: 'Register',
    navitems: [
      {link: '/', content: "Home"},
      {link: '/registerForm', content: "registerForm"}
    ]
  });
});

module.exports = router;
