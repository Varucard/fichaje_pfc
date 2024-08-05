'use strict';
window.FontAwesomeKitConfig = {
  "asyncLoading" : {
    "enabled" : false
  },
  "autoA11y" : {
    "enabled" : true
  },
  "baseUrl" : "https://ka-f.fontawesome.com",
  "baseUrlKit" : "https://kit.fontawesome.com",
  "detectConflictsUntil" : null,
  "iconUploads" : {},
  "id" : 8602681,
  "license" : "free",
  "method" : "css",
  "minify" : {
    "enabled" : true
  },
  "token" : "80123ed9f9",
  "v4FontFaceShim" : {
    "enabled" : true
  },
  "v4shim" : {
    "enabled" : true
  },
  "v5FontFaceShim" : {
    "enabled" : true
  },
  "version" : "6.4.0"
};
!function(race) {
  if ("function" == typeof define && define.amd) {
    define("kit-loader", race);
  } else {
    race();
  }
}(function() {
  /**
   * @param {!Object} obj
   * @param {boolean} topLeft
   * @return {?}
   */
  function assign(obj, topLeft) {
    /** @type {!Array<string>} */
    var result = Object.keys(obj);
    if (Object.getOwnPropertySymbols) {
      /** @type {!Array<?>} */
      var r = Object.getOwnPropertySymbols(obj);
      if (topLeft) {
        /** @type {!Array<?>} */
        r = r.filter(function(key) {
          return Object.getOwnPropertyDescriptor(obj, key).enumerable;
        });
      }
      result.push.apply(result, r);
    }
    return result;
  }
  /**
   * @param {!Function} node
   * @return {?}
   */
  function extend(node) {
    /** @type {number} */
    var i = 1;
    for (; i < arguments.length; i++) {
      var obj = null != arguments[i] ? arguments[i] : {};
      if (i % 2) {
        assign(Object(obj), true).forEach(function(field) {
          build(node, field, obj[field]);
        });
      } else {
        if (Object.getOwnPropertyDescriptors) {
          Object.defineProperties(node, Object.getOwnPropertyDescriptors(obj));
        } else {
          assign(Object(obj)).forEach(function(prop) {
            Object.defineProperty(node, prop, Object.getOwnPropertyDescriptor(obj, prop));
          });
        }
      }
    }
    return node;
  }
  /**
   * @param {!Object} cb
   * @return {?}
   */
  function next(cb) {
    return (next = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function(init) {
      return typeof init;
    } : function(obj) {
      return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj;
    })(cb);
  }
  /**
   * @param {!Function} obj
   * @param {string} key
   * @param {!Object} value
   * @return {?}
   */
  function build(obj, key, value) {
    return (key = function(t) {
      var x = function(obj, type) {
        if ("object" != typeof obj || null === obj) {
          return obj;
        }
        var self = obj[Symbol.toPrimitive];
        if (void 0 !== self) {
          var proto = self.call(obj, type || "default");
          if ("object" != typeof proto) {
            return proto;
          }
          throw new TypeError("@@toPrimitive must return a primitive value.");
        }
        return ("string" === type ? String : Number)(obj);
      }(t, "string");
      return "symbol" == typeof x ? x : String(x);
    }(key)) in obj ? Object.defineProperty(obj, key, {
      value : value,
      enumerable : true,
      configurable : true,
      writable : true
    }) : obj[key] = value, obj;
  }
  /**
   * @param {!Element} v
   * @param {number} e
   * @return {?}
   */
  function update(v, e) {
    return function(val) {
      if (Array.isArray(val)) {
        return val;
      }
    }(v) || function(result, e) {
      var value = null == result ? null : "undefined" != typeof Symbol && result[Symbol.iterator] || result["@@iterator"];
      if (null != value) {
        var result;
        var o;
        var clip;
        var e;
        /** @type {!Array} */
        var d = [];
        /** @type {boolean} */
        var _n = true;
        /** @type {boolean} */
        var f = false;
        try {
          if (clip = (value = value.call(result)).next, 0 === e) {
            if (Object(value) !== value) {
              return;
            }
            /** @type {boolean} */
            _n = false;
          } else {
            for (; !(_n = (result = clip.call(value)).done) && (d.push(result.value), d.length !== e); _n = true) {
            }
          }
        } catch (tObj) {
          /** @type {boolean} */
          f = true;
          o = tObj;
        } finally {
          try {
            if (!_n && null != value.return && (e = value.return(), Object(e) !== e)) {
              return;
            }
          } finally {
            if (f) {
              throw o;
            }
          }
        }
        return d;
      }
    }(v, e) || function(item, count) {
      if (!item) {
        return;
      }
      if ("string" == typeof item) {
        return emit(item, count);
      }
      /** @type {string} */
      var name = Object.prototype.toString.call(item).slice(8, -1);
      if ("Object" === name && item.constructor) {
        name = item.constructor.name;
      }
      if ("Map" === name || "Set" === name) {
        return Array.from(item);
      }
      if ("Arguments" === name || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(name)) {
        return emit(item, count);
      }
    }(v, e) || function() {
      throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.");
    }();
  }
  /**
   * @param {!Object} data
   * @param {number} value
   * @return {?}
   */
  function emit(data, value) {
    if (null == value || value > data.length) {
      value = data.length;
    }
    /** @type {number} */
    var i = 0;
    /** @type {!Array} */
    var e = new Array(value);
    for (; i < value; i++) {
      e[i] = data[i];
    }
    return e;
  }
  /**
   * @param {!Object} defaults
   * @param {string} options
   * @return {?}
   */
  function request(defaults, options) {
    var result = options && options.addOn || "";
    var sessionID = options && options.baseFilename || defaults.license + result;
    /** @type {string} */
    var url = options && options.minify ? ".min" : "";
    var i = options && options.fileSuffix || defaults.method;
    var yunjiao = options && options.subdir || defaults.method;
    return defaults.baseUrl + "/releases/" + ("latest" === defaults.version ? "latest" : "v".concat(defaults.version)) + "/" + yunjiao + "/" + sessionID + url + "." + i;
  }
  /**
   * @param {!Object} options
   * @return {?}
   */
  function parse(options) {
    return options.baseUrlKit + "/" + options.token + "/" + options.id + "/kit-upload.css";
  }
  /**
   * @param {!Object} context
   * @param {number} timeout
   * @return {undefined}
   */
  function render(context, timeout) {
    var timer = timeout || ["fa"];
    /** @type {string} */
    var id = "." + Array.prototype.join.call(timer, ",.");
    var vertexSet = context.querySelectorAll(id);
    Array.prototype.forEach.call(vertexSet, function(current) {
      var n = current.getAttribute("title");
      current.setAttribute("aria-hidden", "true");
      /** @type {boolean} */
      var e = !current.nextElementSibling || !current.nextElementSibling.classList.contains("sr-only");
      if (n && e) {
        var item = context.createElement("span");
        item.innerHTML = n;
        item.classList.add("sr-only");
        current.parentNode.insertBefore(item, current.nextSibling);
      }
    });
  }
  /**
   * @return {undefined}
   */
  function initialHandler() {
    /** @type {number} */
    var i = 0;
    for (; i < queue.length; i++) {
      queue[i][0](queue[i][1]);
    }
    /** @type {!Array} */
    queue = [];
    /** @type {boolean} */
    f = false;
  }
  /**
   * @param {!Function} callback
   * @param {!Object} arg
   * @return {undefined}
   */
  function cb(callback, arg) {
    queue.push([callback, arg]);
    if (!f) {
      /** @type {boolean} */
      f = true;
      call(initialHandler, 0);
    }
  }
  /**
   * @param {!Object} object
   * @return {undefined}
   */
  function resolve(object) {
    var p = object.owner;
    var state = p._state;
    var data = p._data;
    var value = object[state];
    var c = object.then;
    if ("function" == typeof value) {
      /** @type {string} */
      state = "fulfilled";
      try {
        data = value(data);
      } catch (value) {
        done(c, value);
      }
    }
    if (!put(c, data)) {
      if ("fulfilled" === state) {
        write(c, data);
      }
      if ("rejected" === state) {
        done(c, data);
      }
    }
  }
  /**
   * @param {!Object} x
   * @param {!Object} obj
   * @return {?}
   */
  function put(x, obj) {
    var addAllMailAddresses;
    try {
      if (x === obj) {
        throw new TypeError("A promises callback cannot return that same promise.");
      }
      if (obj && ("function" == typeof obj || "object" === next(obj))) {
        var c = obj.then;
        if ("function" == typeof c) {
          return c.call(obj, function(value) {
            if (!addAllMailAddresses) {
              /** @type {boolean} */
              addAllMailAddresses = true;
              if (obj === value) {
                reject(x, value);
              } else {
                write(x, value);
              }
            }
          }, function(err) {
            if (!addAllMailAddresses) {
              /** @type {boolean} */
              addAllMailAddresses = true;
              done(x, err);
            }
          }), true;
        }
      }
    } catch (text) {
      return addAllMailAddresses || done(x, text), true;
    }
    return false;
  }
  /**
   * @param {!Object} key
   * @param {(Object|string)} state
   * @return {undefined}
   */
  function write(key, state) {
    if (!(key !== state && put(key, state))) {
      reject(key, state);
    }
  }
  /**
   * @param {!Object} obj
   * @param {(Object|string)} data
   * @return {undefined}
   */
  function reject(obj, data) {
    if ("pending" === obj._state) {
      /** @type {string} */
      obj._state = "settled";
      /** @type {(Object|string)} */
      obj._data = data;
      cb(publishFulfillment, obj);
    }
  }
  /**
   * @param {!Object} obj
   * @param {(Object|string)} data
   * @return {undefined}
   */
  function done(obj, data) {
    if ("pending" === obj._state) {
      /** @type {string} */
      obj._state = "settled";
      /** @type {(Object|string)} */
      obj._data = data;
      cb(handler, obj);
    }
  }
  /**
   * @param {!Object} promise
   * @return {undefined}
   */
  function publish(promise) {
    promise._then = promise._then.forEach(resolve);
  }
  /**
   * @param {!Object} promise
   * @return {undefined}
   */
  function publishFulfillment(promise) {
    /** @type {string} */
    promise._state = "fulfilled";
    publish(promise);
  }
  /**
   * @param {!Object} promise
   * @return {undefined}
   */
  function handler(promise) {
    /** @type {string} */
    promise._state = "rejected";
    publish(promise);
    if (!promise._handled && lastLI) {
      global.process.emit("unhandledRejection", promise._data, promise);
    }
  }
  /**
   * @param {?} value
   * @return {undefined}
   */
  function e(value) {
    global.process.emit("rejectionHandled", value);
  }
  /**
   * @param {!Object} fn
   * @return {undefined}
   */
  function c(fn) {
    if ("function" != typeof fn) {
      throw new TypeError("Promise resolver " + fn + " is not a function");
    }
    if (this instanceof c == false) {
      throw new TypeError("Failed to construct 'Promise': Please use the 'new' operator, this object constructor cannot be called as a function.");
    }
    /** @type {!Array} */
    this._then = [];
    (function(callback, source) {
      /**
       * @param {(Object|string)} m
       * @return {undefined}
       */
      function f(m) {
        done(source, m);
      }
      try {
        callback(function(block) {
          write(source, block);
        }, f);
      } catch (_message) {
        f(_message);
      }
    })(fn, this);
  }
  /**
   * @param {string} location
   * @param {!Object} e
   * @return {?}
   */
  function init(location, e) {
    var cb = e.fetch;
    var xhr = e.XMLHttpRequest;
    var key = e.token;
    /** @type {string} */
    var url = location;
    return "URLSearchParams" in window ? (url = new URL(location)).searchParams.set("token", key) : url = url + "?token=" + encodeURIComponent(key), url = url.toString(), new Map(function(callback, reject) {
      if ("function" == typeof cb) {
        cb(url, {
          mode : "cors",
          cache : "default"
        }).then(function(result) {
          if (result.ok) {
            return result.text();
          }
          throw new Error("");
        }).then(function(identifierPositions) {
          callback(identifierPositions);
        }).catch(reject);
      } else {
        if ("function" == typeof xhr) {
          var req = new xhr;
          req.addEventListener("loadend", function() {
            if (this.responseText) {
              callback(this.responseText);
            } else {
              reject(new Error(""));
            }
          });
          ["abort", "error", "timeout"].map(function(t) {
            req.addEventListener(t, function() {
              reject(new Error(""));
            });
          });
          req.open("GET", url);
          req.send();
        } else {
          reject(new Error(""));
        }
      }
    });
  }
  /**
   * @param {!Object} type
   * @param {?} data
   * @param {?} pluginName
   * @return {?}
   */
  function get(type, data, pluginName) {
    /** @type {!Object} */
    var label = type;
    return [[/(url\("?)\.\.\/\.\.\/\.\./g, function(canCreateDiscussions, mm) {
      return "".concat(mm).concat(data);
    }], [/(url\("?)\.\.\/webfonts/g, function(canCreateDiscussions, mm) {
      return "".concat(mm).concat(data, "/releases/v").concat(pluginName, "/webfonts");
    }], [/(url\("?)https:\/\/kit-free([^.])*\.fontawesome\.com/g, function(canCreateDiscussions, mm) {
      return "".concat(mm).concat(data);
    }]].forEach(function(obj) {
      var res = update(obj, 2);
      var key = res[0];
      var item = res[1];
      label = label.replace(key, item);
    }), label;
  }
  /**
   * @param {!Object} options
   * @param {!Window} context
   * @return {?}
   */
  function create(options, context) {
    var internalizeProducer = arguments.length > 2 && void 0 !== arguments[2] ? arguments[2] : function() {
    };
    var document = context.document || document;
    var result = render.bind(render, document, ["fa", "fab", "fas", "far", "fal", "fad", "fak"]);
    /** @type {boolean} */
    var f = Object.keys(options.iconUploads || {}).length > 0;
    if (options.autoA11y.enabled) {
      internalizeProducer(result);
    }
    /** @type {!Array} */
    var s = [{
      id : "fa-main",
      addOn : void 0
    }];
    if (options.v4shim && options.v4shim.enabled) {
      s.push({
        id : "fa-v4-shims",
        addOn : "-v4-shims"
      });
    }
    if (options.v5FontFaceShim && options.v5FontFaceShim.enabled) {
      s.push({
        id : "fa-v5-font-face",
        addOn : "-v5-font-face"
      });
    }
    if (options.v4FontFaceShim && options.v4FontFaceShim.enabled) {
      s.push({
        id : "fa-v4-font-face",
        addOn : "-v4-font-face"
      });
    }
    if (f) {
      s.push({
        id : "fa-kit-upload",
        customCss : true
      });
    }
    /** @type {!Array<?>} */
    var value = s.map(function(req) {
      return new Map(function(when, reject) {
        init(req.customCss ? parse(options) : request(options, {
          addOn : req.addOn,
          minify : options.minify.enabled
        }), context).then(function(body) {
          when(callback(body, extend(extend({}, context), {}, {
            baseUrl : options.baseUrl,
            version : options.version,
            id : req.id,
            contentFilter : function(content, items) {
              return get(content, items.baseUrl, items.version);
            }
          })));
        }).catch(reject);
      });
    });
    return Map.all(value);
  }
  /**
   * @param {!Object} content
   * @param {string} options
   * @return {?}
   */
  function callback(content, options) {
    var text = options.contentFilter || function(err, withHashes) {
      return err;
    };
    /** @type {!Element} */
    var obj = document.createElement("style");
    /** @type {!Text} */
    var o = document.createTextNode(text(content, options));
    return obj.appendChild(o), obj.media = "all", options.id && obj.setAttribute("id", options.id), options && options.detectingConflicts && options.detectionIgnoreAttr && obj.setAttributeNode(document.createAttribute(options.detectionIgnoreAttr)), obj;
  }
  /**
   * @param {!Object} options
   * @param {undefined} data
   * @return {?}
   */
  function run(options, data) {
    data.autoA11y = options.autoA11y.enabled;
    if ("pro" === options.license) {
      /** @type {boolean} */
      data.autoFetchSvg = true;
      /** @type {string} */
      data.fetchSvgFrom = options.baseUrl + "/releases/" + ("latest" === options.version ? "latest" : "v".concat(options.version)) + "/svgs";
      data.fetchUploadedSvgFrom = options.uploadsUrl;
    }
    /** @type {!Array} */
    var self = [];
    return options.v4shim.enabled && self.push(new Map(function(cb, reject) {
      init(request(options, {
        addOn : "-v4-shims",
        minify : options.minify.enabled
      }), data).then(function(filename) {
        cb(test(filename, extend(extend({}, data), {}, {
          id : "fa-v4-shims"
        })));
      }).catch(reject);
    })), self.push(new Map(function(dispatch, reject) {
      init(request(options, {
        minify : options.minify.enabled
      }), data).then(function(then) {
        var error = test(then, extend(extend({}, data), {}, {
          id : "fa-main"
        }));
        dispatch(function(element, data) {
          var n = data && void 0 !== data.autoFetchSvg ? data.autoFetchSvg : void 0;
          var v = data && void 0 !== data.autoA11y ? data.autoA11y : void 0;
          if (void 0 !== v) {
            element.setAttribute("data-auto-a11y", v ? "true" : "false");
          }
          if (n) {
            element.setAttributeNode(document.createAttribute("data-auto-fetch-svg"));
            element.setAttribute("data-fetch-svg-from", data.fetchSvgFrom);
            element.setAttribute("data-fetch-uploaded-svg-from", data.fetchUploadedSvgFrom);
          }
          return element;
        }(error, data));
      }).catch(reject);
    })), Map.all(self);
  }
  /**
   * @param {?} text
   * @param {string} options
   * @return {?}
   */
  function test(text, options) {
    /** @type {!Element} */
    var el = document.createElement("SCRIPT");
    /** @type {!Text} */
    var js = document.createTextNode(text);
    return el.appendChild(js), el.referrerPolicy = "strict-origin", options.id && el.setAttribute("id", options.id), options && options.detectingConflicts && options.detectionIgnoreAttr && el.setAttributeNode(document.createAttribute(options.detectionIgnoreAttr)), el;
  }
  /**
   * @param {!Function} f
   * @return {undefined}
   */
  function onReady(f) {
    var fn;
    /** @type {!Array} */
    var afterFlushCallbacks = [];
    /** @type {!HTMLDocument} */
    var doc = document;
    /** @type {function(this:Element, string=): undefined} */
    var hack = doc.documentElement.doScroll;
    /** @type {boolean} */
    var i = (hack ? /^loaded|^c/ : /^loaded|^i|^c/).test(doc.readyState);
    if (!i) {
      doc.addEventListener("DOMContentLoaded", fn = function() {
        doc.removeEventListener("DOMContentLoaded", fn);
        /** @type {number} */
        i = 1;
        for (; fn = afterFlushCallbacks.shift();) {
          fn();
        }
      });
    }
    if (i) {
      setTimeout(f, 0);
    } else {
      afterFlushCallbacks.push(f);
    }
  }
  /**
   * @param {?} callback
   * @return {undefined}
   */
  function checkElement(callback) {
    if ("undefined" != typeof MutationObserver) {
      (new MutationObserver(callback)).observe(document, {
        childList : true,
        subtree : true
      });
    }
  }
  var f;
  /**
   * @return {undefined}
   */
  var NOOP = function() {
  };
  /** @type {boolean} */
  var lastLI = "undefined" != typeof global && void 0 !== global.process && "function" == typeof global.process.emit;
  /** @type {!Function} */
  var call = "undefined" == typeof setImmediate ? setTimeout : setImmediate;
  /** @type {!Array} */
  var queue = [];
  c.prototype = {
    constructor : c,
    _state : "pending",
    _then : null,
    _data : void 0,
    _handled : false,
    then : function(onFulfillment, onRejection) {
      var subscriber = {
        owner : this,
        then : new this.constructor(NOOP),
        fulfilled : onFulfillment,
        rejected : onRejection
      };
      return !onRejection && !onFulfillment || this._handled || (this._handled = true, "rejected" === this._state && lastLI && cb(e, this)), "fulfilled" === this._state || "rejected" === this._state ? cb(resolve, subscriber) : this._then.push(subscriber), subscriber.then;
    },
    catch : function(onRejection) {
      return this.then(null, onRejection);
    }
  };
  /**
   * @param {!Array} obj
   * @return {?}
   */
  c.all = function(obj) {
    if (!Array.isArray(obj)) {
      throw new TypeError("You must pass an array to Promise.all().");
    }
    return new c(function(callback, reject) {
      /**
       * @param {number} i
       * @return {?}
       */
      function resolver(i) {
        return o++, function(_$param0) {
          r[i] = _$param0;
          if (!--o) {
            callback(r);
          }
        };
      }
      /** @type {!Array} */
      var r = [];
      /** @type {number} */
      var o = 0;
      var result;
      /** @type {number} */
      var i = 0;
      for (; i < obj.length; i++) {
        if ((result = obj[i]) && "function" == typeof result.then) {
          result.then(resolver(i), reject);
        } else {
          r[i] = result;
        }
      }
      if (!o) {
        callback(r);
      }
    });
  };
  /**
   * @param {!NodeList} a
   * @return {?}
   */
  c.race = function(a) {
    if (!Array.isArray(a)) {
      throw new TypeError("You must pass an array to Promise.race().");
    }
    return new c(function(resolve, reject) {
      var e;
      /** @type {number} */
      var i = 0;
      for (; i < a.length; i++) {
        if ((e = a[i]) && "function" == typeof e.then) {
          e.then(resolve, reject);
        } else {
          resolve(e);
        }
      }
    });
  };
  /**
   * @param {!Object} o
   * @return {?}
   */
  c.resolve = function(o) {
    return o && "object" === next(o) && o.constructor === c ? o : new c(function(callback) {
      callback(o);
    });
  };
  /**
   * @param {?} reason
   * @return {?}
   */
  c.reject = function(reason) {
    return new c(function(canCreateDiscussions, reject$2) {
      reject$2(reason);
    });
  };
  /** @type {!Function} */
  var Map = "function" == typeof Promise ? Promise : c;
  try {
    if (window.FontAwesomeKitConfig) {
      var node = window.FontAwesomeKitConfig;
      var params = {
        detectingConflicts : node.detectConflictsUntil && new Date <= new Date(node.detectConflictsUntil),
        detectionIgnoreAttr : "data-fa-detection-ignore",
        fetch : window.fetch,
        token : node.token,
        XMLHttpRequest : window.XMLHttpRequest,
        document : document
      };
      /** @type {(HTMLScriptElement|null)} */
      var el = document.currentScript;
      /** @type {(Element|null)} */
      var output = el ? el.parentElement : document.head;
      (function() {
        var config = arguments.length > 0 && void 0 !== arguments[0] ? arguments[0] : {};
        var callback = arguments.length > 1 && void 0 !== arguments[1] ? arguments[1] : {};
        return "js" === config.method ? run(config, callback) : "css" === config.method ? create(config, callback, function(run) {
          onReady(run);
          checkElement(run);
        }) : void 0;
      })(node, params).then(function(buildInTemplates) {
        buildInTemplates.map(function(text) {
          try {
            output.insertBefore(text, el ? el.nextSibling : null);
          } catch (e) {
            output.appendChild(text);
          }
        });
        if (params.detectingConflicts && el) {
          onReady(function() {
            el.setAttributeNode(document.createAttribute(params.detectionIgnoreAttr));
            var t = function(options, route) {
              /** @type {!Element} */
              var node = document.createElement("script");
              return route && route.detectionIgnoreAttr && node.setAttributeNode(document.createAttribute(route.detectionIgnoreAttr)), node.src = request(options, {
                baseFilename : "conflict-detection",
                fileSuffix : "js",
                subdir : "js",
                minify : options.minify.enabled
              }), node;
            }(node, params);
            document.body.appendChild(t);
          });
        }
      }).catch(function(text) {
        console.error("".concat("Font Awesome Kit:", " ").concat(text));
      });
    }
  } catch (expected) {
    console.error("".concat("Font Awesome Kit:", " ").concat(expected));
  }
});