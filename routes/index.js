var express = require('express');
var router = express.Router();
var fs = require('fs');
var MongoClient = require('mongodb').MongoClient;
var objectId = require('mongodb').ObjectID;
var assert = require('assert');

var url = 'mongodb://127.0.0.1:27017/test';

User = require('../models/user.js');

/* GET home page. */
router.get('/', function(req, res, next) {
  res.render('index', {
    title: 'Home',
    navitems: [
      {link: '/', content: 'Home'},
      {link: '/registerForm', content: 'Register'},
      {link: '/getUsers', content: 'Users'}
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
      {link: '/registerForm', content: 'Register'},
      {link: '/getUsers', content: 'Users'}
    ]
  });
});

/* GET users page. */
router.get('/getUsers', function(req, res, next) {
  var resultArray = [];
  MongoClient.connect(url, function(err, db) {
    assert.equal(null, err);
    var cursor = db.collection('users').find();
    cursor.forEach(function(doc, err){
      assert.equal(null, err);
      resultArray.push(doc);
    }, function(){
      db.close();
      res.render('users', {
        title: 'Users',
        users: resultArray,
        navitems: [
          {link: '/', content: 'Home'},
          {link: '/registerForm', content: 'Register'},
          {link: '/getUsers', content: 'Users'}
        ]
      })
    })
  });
});

/* GET home page. */
router.get('/add', function(req, res, next) {
  res.render('add', {
    title: 'Add User',
    navitems: [
      {link: '/', content: 'Home'},
      {link: '/registerForm', content: 'Register'},
      {link: '/getUsers', content: 'Users'}
    ]
  });
});

router.post('/addUser', function(req, res, next){
  var user = {
    firstname: req.body.firstname,
    lastname: req.body.lastname,
    email: req.body.email
  };

  MongoClient.connect(url, function(err, db) {
    assert.equal(null, err);
    db.collection('users').insertOne(user, function(err, result) {
      assert.equal(null, err);
      console.log('User inserted');
      db.close();
    });
  });

  res.redirect('/users');
});

router.get('/editUser/:id', function(req, res, next) {
  var id = req.params.id;
  var resultArray = [];
  MongoClient.connect(url, function(err, db) {
    assert.equal(null, err);
    var cursor = db.collection('users').findOne({"_id":objectId(id)});
    cursor.forEach(function(doc, err){
      assert.equal(null, err);
      resultArray.push(doc);
    }, function(){
      db.close();
      res.render('editUser', {
        title: 'Edit User',
        users: resultArray,
        navitems: [
          {link: '/', content: 'Home'},
          {link: '/registerForm', content: 'Register'},
          {link: '/getUsers', content: 'Users'}
        ]
      })
    })
  });
});

/* GET home page. */
router.post('/edit', function(req, res, next) {
  var user = {
    firstname: req.body.firstname,
    lastname: req.body.lastname,
    email: req.body.email
  };

  var id = req.body.id;

  MongoClient.connect(url, function(err, db) {
    assert.equal(null, err);
    db.collection('users').updateOne({"_id":ObjectId(id)}, {$set: user},function(err, result) {
      assert.equal(null, err);
      console.log('User updated');
      db.close();
    });
  });
});

/* GET home page. */
router.get('/deleteUser', function(req, res, next) {
  var id = req.body.id;

  MongoClient.connect(url, function(err, db) {
    assert.equal(null, err);
    db.collection('users').deleteOne({"_id":objectId(id)},function(err, result) {
      assert.equal(null, err);
      console.log('User deleted');
      db.close();
    });
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
