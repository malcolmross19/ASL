var express = require('express');
var router = express.Router();
var fs = require('fs');

/* GET home page. */
router.get('/', function(req, res, next) {
  res.render('index', {
    title: 'Home',
    navitems: [
      {link: '/', content: 'Home'},
      {link: '/registerForm', content: 'Register'}
    ]
  });
});

/* GET registerForm page */
router.get('/registerForm', function(req, res, next) {
  res.render('registerForm', {
    title: 'Register',
    success: req.session.success,
    errors: req.session.errors,
    navitems: [
      {link: '/', content: 'Home'},
      {link: '/registerForm', content: 'Register'}
    ]
  });
});

router.post('/registerForm', function(req, res, next){
  // Check validity
  req.check('name', 'You can not leave this blank').isEmpty();
  req.check('email', 'Password is invalid').isLength({min: 4});

  var errors = req.validationErrors();
  if (errors){
    req.session.errors = errors;
    req.session.success = false;
  }else{
    req.session.success = true;
  }

  res.redirect('/registerForm');
})

module.exports = router;
