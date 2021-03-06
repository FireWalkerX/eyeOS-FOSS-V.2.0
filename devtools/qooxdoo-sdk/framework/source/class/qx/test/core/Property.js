/* ************************************************************************

   qooxdoo - the new era of web development

   http://qooxdoo.org

   Copyright:
     2007-2008 1&1 Internet AG, Germany, http://www.1und1.de

   License:
     LGPL: http://www.gnu.org/licenses/lgpl.html
     EPL: http://www.eclipse.org/org/documents/epl-v10.php
     See the LICENSE file in the project's top-level directory for details.

   Authors:
     * Fabian Jakobs (fjakobs)

************************************************************************ */

qx.Class.define("qx.test.core.Property",
{
  extend : qx.dev.unit.TestCase,

  members :
  {
    testBasic : function()
    {
      this.debug("Exec: testBasic");

      this.assertNotUndefined(qx.core.Property);

      // Check instance
      var inst = new qx.test.core.PropertyHelper;
      this.assertNotUndefined(inst, "instance");

      // Public setter/getter etc.
      this.assertFunction(inst.setPublicProp, "public setter");
      this.assertFunction(inst.getPublicProp, "public getter");
      this.assertFunction(inst.resetPublicProp, "public reset");
      this.assertUndefined(inst.togglePublicProp, "public toggler");
      this.assertUndefined(inst.setThemedPublicProp, "public themed");

      // Boolean property
      this.assertFunction(inst.toggleBooleanProp, "boolean toggler");

      this.debug("Done: testBasic");
    },


    testBuiltinTypes : function()
    {
      this.debug("Exec: testBuiltinTypes");

      this.assertNotUndefined(qx.core.Property);

      // Check instance
      var inst = new qx.test.core.PropertyHelper;
      this.assertNotUndefined(inst, "instance");

      // Type checks: String
      this.assertIdentical("Hello", inst.setStringProp("Hello"), "string property, set");
      this.assertIdentical("Hello", inst.getStringProp(), "string property, get");

      // Type checks: Boolean, true
      this.assertIdentical(true, inst.setBooleanProp(true), "boolean property, set");
      this.assertIdentical(true, inst.getBooleanProp(), "boolean property, get");

      // Type checks: Boolean, false
      this.assertIdentical(false, inst.setBooleanProp(false), "boolean property, set");
      this.assertIdentical(false, inst.getBooleanProp(), "boolean property, get");

      // Type checks: Number, int
      this.assertIdentical(3, inst.setNumberProp(3), "number property, set");
      this.assertIdentical(3, inst.getNumberProp(), "number property, get");

      // Type checks: Number, float
      this.assertIdentical(3.14, inst.setNumberProp(3.14), "number property, set");
      this.assertIdentical(3.14, inst.getNumberProp(), "number property, get");

      // Type checks: Object, inline
      var obj = {};
      this.assertIdentical(obj, inst.setObjectProp(obj), "object property, set");
      this.assertIdentical(obj, inst.getObjectProp(), "object property, get");

      // Type checks: Object, new
      var obj = new Object;
      this.assertIdentical(obj, inst.setObjectProp(obj), "object property, set");
      this.assertIdentical(obj, inst.getObjectProp(), "object property, get");

      // Type checks: Array, inline
      var arr = [];
      this.assertIdentical(arr, inst.setArrayProp(arr), "array property, set");
      this.assertIdentical(arr, inst.getArrayProp(), "array property, get");

      // Type checks: Array, new
      var arr = new Array;
      this.assertIdentical(arr, inst.setArrayProp(arr), "array property, set");
      this.assertIdentical(arr, inst.getArrayProp(), "array property, get");

      this.debug("Done: testBuiltinTypes");
    },


    testInheritance : function()
    {
      this.debug("Exec: testInheritance");

      this.assertNotUndefined(qx.core.Property);

      var pa = new qx.test.core.InheritanceDummy();
      var ch1 = new qx.test.core.InheritanceDummy();
      var ch2 = new qx.test.core.InheritanceDummy();
      var ch3 = new qx.test.core.InheritanceDummy();
      var chh1 = new qx.test.core.InheritanceDummy();
      var chh2 = new qx.test.core.InheritanceDummy();
      var chh3 = new qx.test.core.InheritanceDummy();

      pa.add(ch1)
      pa.add(ch2);
      pa.add(ch3);
      ch2.add(chh1);
      ch2.add(chh2);
      ch2.add(chh3);

      // Simple: Only inheritance, no local values
      this.assertTrue(pa.setEnabled(true), "a1");
      this.assertTrue(pa.getEnabled(), "a2");
      this.assertTrue(ch1.getEnabled(), "a3");
      this.assertTrue(ch2.getEnabled(), "a4");
      this.assertTrue(ch3.getEnabled(), "a5");
      this.assertTrue(chh1.getEnabled(), "a6");
      this.assertTrue(chh2.getEnabled(), "a7");
      this.assertTrue(chh3.getEnabled(), "a8");

      // Enabling local value
      this.assertFalse(ch2.setEnabled(false), "b1");
      this.assertFalse(ch2.getEnabled(), "b2");
      this.assertFalse(chh1.getEnabled(), "b3");
      this.assertFalse(chh2.getEnabled(), "b4");
      this.assertFalse(chh3.getEnabled(), "b5");

      // Reset local value
      this.assertUndefined(ch2.resetEnabled(), "c1");
      this.assertTrue(ch2.getEnabled(), "c2");
      this.assertTrue(chh1.getEnabled(), "c3");
      this.assertTrue(chh2.getEnabled(), "c4");
      this.assertTrue(chh3.getEnabled(), "c5");

      this.debug("Done: testInheritance");
    },


    testParent : function()
    {
      var pa = new qx.test.core.InheritanceDummy();
      var ch1 = new qx.test.core.InheritanceDummy();
      var ch2 = new qx.test.core.InheritanceDummy();
      var ch3 = new qx.test.core.InheritanceDummy();

      this.assertIdentical(pa.getEnabled(), null, "d1");
      this.assertIdentical(ch1.getEnabled(), null, "d2");
      this.assertIdentical(ch2.getEnabled(), null, "d3");
      this.assertIdentical(ch3.getEnabled(), null, "d4");

      pa.add(ch1);

      this.assertTrue(pa.setEnabled(true), "a1");  // ch1 gets enabled, too
      this.assertFalse(ch3.setEnabled(false), "a2");

      this.assertTrue(pa.getEnabled(), "b1");
      this.assertTrue(ch1.getEnabled(), "b2");
      this.assertIdentical(ch2.getEnabled(), null, "b3");
      this.assertFalse(ch3.getEnabled(), "b4");

      pa.add(ch2);  // make ch2 enabled_ through inheritance
      pa.add(ch3);  // keep ch2 disabled, user value has higher priority

      this.assertTrue(pa.getEnabled(), "c1");
      this.assertTrue(ch1.getEnabled(), "c2");
      this.assertTrue(ch2.getEnabled(), "c3");
      this.assertFalse(ch3.getEnabled(), "c4");
    },


    testMultiValues : function()
    {
      this.debug("Exec: testMultiValues");

      this.assertNotUndefined(qx.core.Property);

      // Check instance
      var inst = new qx.test.core.PropertyHelper;
      this.assertNotUndefined(inst, "instance");

      // Check init value
      this.assertIdentical(inst.getInitProp(), "foo", "a1");
      this.assertIdentical(inst.setInitProp("hello"), "hello", "a2");
      this.assertIdentical(inst.getInitProp(), "hello", "a3");
      this.assertIdentical(inst.resetInitProp(), undefined, "a4");
      this.assertIdentical(inst.getInitProp(), "foo", "a5");

      // Check null value
      this.assertIdentical(inst.getNullProp(), "bar", "b1");
      this.assertIdentical(inst.setNullProp("hello"), "hello", "b2");
      this.assertIdentical(inst.getNullProp(), "hello", "b3");
      this.assertIdentical(inst.setNullProp(null), null, "b4");
      this.assertIdentical(inst.getNullProp(), null, "b5");
      this.assertIdentical(inst.resetNullProp(), undefined, "b6");
      this.assertIdentical(inst.getNullProp(), "bar", "b7");

      // Check appearance value
      this.assertIdentical(inst.setThemedAppearanceProp("black"), "black", "c1");
      this.assertIdentical(inst.getAppearanceProp(), "black", "c2");
      this.assertIdentical(inst.setAppearanceProp("white"), "white", "c3");
      this.assertIdentical(inst.getAppearanceProp(), "white", "c4");
      this.assertIdentical(inst.resetAppearanceProp(), undefined, "c5");
      this.assertIdentical(inst.getAppearanceProp(), "black", "c6");

      // No prop
      this.assertIdentical(inst.getNoProp(), null, "c1");

      this.debug("Done: testMultiValues");
    },


    testInitApply : function()
    {
      var inst = new qx.test.core.PropertyHelper;
      this.assertNotUndefined(inst, "instance");

      this.assertUndefined(inst.lastApply);
      inst.setInitApplyProp1("juhu"); //set to init value
      this.assertJsonEquals(["juhu", "juhu"], inst.lastApply);
      inst.lastApply = undefined;

      inst.setInitApplyProp1("juhu"); // set to same value
      this.assertUndefined(inst.lastApply); // apply must not be called
      inst.lastApply = undefined;

      inst.setInitApplyProp1("kinners"); // set to new value
      this.assertJsonEquals(["kinners", "juhu"], inst.lastApply);
      inst.lastApply = undefined;

      this.assertUndefined(inst.lastApply);
      inst.setInitApplyProp2(null); //set to init value
      this.assertJsonEquals([null, null], inst.lastApply);
      inst.lastApply = undefined;
    },


    testInit : function()
    {
      // now test the init functions
      var self = this;
      var inst = new qx.test.core.PropertyHelper(function(inst) {

        inst.initInitApplyProp1();
        self.assertJsonEquals(["juhu", null], inst.lastApply);
        inst.lastApply = undefined;

        inst.initInitApplyProp2();
        self.assertJsonEquals([null, null], inst.lastApply);
        inst.lastApply = undefined;
      });
      this.assertNotUndefined(inst, "instance");
    },

    testDefinesThanSubClassWithInterface : function()
    {
      // see bug #2162 for details
      delete qx.test.A;
      delete qx.test.B;
      delete qx.test.IForm;

      qx.Class.define("qx.test.A",
      {
        extend : qx.core.Object,

        properties : {
          enabled : {}
        }
      });

      var a = new qx.test.A();

      qx.Interface.define("qx.test.IForm",
      {
        members : {
          setEnabled : function(value) {}
        }
      });

      qx.Class.define("qx.test.B",
      {
        extend : qx.test.A,
        implement : qx.test.IForm
      });

      var b = new qx.test.B();
      b.setEnabled(true);
    },


    testPropertyNamedClassname : function()
    {
      qx.Class.define("qx.test.clName", {
        extend : qx.core.Object,
        properties : {
          classname : {}
        }
      });

      delete qx.test.clName;
    },


    testWrongPropertyDefinitions : function()
    {
      if (qx.core.Variant.isSet("qx.debug", "on")) {
        // date
        this.assertException(function() {
          qx.Class.define("qx.test.clName", {
            extend : qx.core.Object,
            properties : new Date()
          });
        }, Error, new RegExp(".*Invalid.*"), "123");
        delete qx.test.clName;

        // array
        this.assertException(function() {
          qx.Class.define("qx.test.clName", {
            extend : qx.core.Object,
            properties : [1,2,3]
          });
        }, Error, new RegExp(".*Invalid.*"), "123");
        delete qx.test.clName;

        // qooxdoo class
        this.assertException(function() {
          qx.Class.define("qx.test.clName", {
            extend : qx.core.Object,
            properties : new qx.core.Object()
          });
        }, Error, new RegExp(".*Invalid.*"), "123");
        delete qx.test.clName;

        // RegExp
        this.assertException(function() {
          qx.Class.define("qx.test.clName", {
            extend : qx.core.Object,
            properties : new RegExp()
          });
        }, Error, new RegExp(".*Invalid.*"), "123");
        delete qx.test.clName;

        // null
        this.assertException(function() {
          qx.Class.define("qx.test.clName", {
            extend : qx.core.Object,
            properties : null
          });
        }, Error, new RegExp(".*Invalid.*"), "123");
        delete qx.test.clName;

        // boolean
        this.assertException(function() {
          qx.Class.define("qx.test.clName", {
            extend : qx.core.Object,
            properties : true
          });
        }, Error, new RegExp(".*Invalid.*"), "123");
        delete qx.test.clName;

        // number
        this.assertException(function() {
          qx.Class.define("qx.test.clName", {
            extend : qx.core.Object,
            properties : 123
          });
        }, Error, new RegExp(".*Invalid.*"), "123");
        delete qx.test.clName;
      }
    },

    testRecursive : function()
    {
      qx.Class.define("qx.Node",
      {
        extend : qx.core.Object,

        construct : function() {
          this._min = 0;
        },

        properties :
        {
          value : { apply : "applyValue" }
        },

        members :
        {

          applyValue: function(value, old) {
            if (value < this._min) {
              this.setValue(this._min);
            }
          }
        }
      });

      var root = new qx.Node();

      root.setValue(100);
      this.assertEquals(100, root.getValue());

      root.setValue(-100);
      this.assertEquals(0, root.getValue());

    },


    testEventWithInitOldData: function()
    {
      qx.Class.define("qx.TestProperty",
      {
        extend : qx.core.Object,

        properties :
        {
          prop : {
            check : "Boolean",
            init : false,
            event : "changeProp"
          }
        }
      });

      var object = new qx.TestProperty();

      // test for the default (false)
      this.assertFalse(object.getProp());

      // check for the event
      var self = this;
      this.assertEventFired(object, "changeProp", function () {
        object.setProp(true);
      }, function(e) {
        self.assertTrue(e.getData(), "Wrong data in the event!");
        self.assertFalse(e.getOldData(), "Wrong old data in the event!");
      }, "Change event not fired!");
    },


    testEventWithoutInitOldData: function()
    {
      qx.Class.define("qx.TestProperty",
      {
        extend : qx.core.Object,

        properties :
        {
          prop : {
            check : "Boolean",
            nullable : true,
            event : "changeProp"
          }
        }
      });

      var object = new qx.TestProperty();

      // test for the default (false)
      this.assertNull(object.getProp());

      // check for the event
      var self = this;
      this.assertEventFired(object, "changeProp", function () {
        object.setProp(true);
      }, function(e) {
        self.assertTrue(e.getData(), "Wrong data in the event!");
        self.assertNull(e.getOldData(), "Wrong old data in the event!");
      }, "Change event not fired!");
    },


    testEventWithInitAndInheritableOldData: function()
    {
      qx.Class.define("qx.TestProperty",
      {
        extend : qx.core.Object,

        properties :
        {
          prop : {
            check : "Boolean",
            init : false,
            inheritable : true,
            event : "changeProp"
          }
        }
      });

      var object = new qx.TestProperty();

      // test for the default (false)
      this.assertFalse(object.getProp());

      // check for the event
      var self = this;
      this.assertEventFired(object, "changeProp", function () {
        object.setProp(true);
      }, function(e) {
        self.assertTrue(e.getData(), "Wrong data in the event!");
        self.assertFalse(e.getOldData(), "Wrong old data in the event!");
      }, "Change event not fired!");
    },


    testEventWithoutInitAndInheritableOldData: function()
    {
      qx.Class.define("qx.TestProperty",
      {
        extend : qx.core.Object,

        properties :
        {
          prop : {
            check : "Boolean",
            nullable : true,
            inheritable : true,
            event : "changeProp"
          }
        }
      });

      var object = new qx.TestProperty();

      // test for the default (false)
      this.assertNull(object.getProp());

      // check for the event
      var self = this;
      this.assertEventFired(object, "changeProp", function () {
        object.setProp(true);
      }, function(e) {
        self.assertTrue(e.getData(), "Wrong data in the event!");
        self.assertNull(e.getOldData(), "Wrong old data in the event!");
      }, "Change event not fired!");
    }
  }
});
