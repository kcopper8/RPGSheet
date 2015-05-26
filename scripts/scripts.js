(function($, _, _s) {
	// Element Util


	function isRepeatingFieldset(el) {
		return _s.startsWith(el.attr('class'), "repeating_");
	}

	function isThisTagAttributeTag($tag) {
		return _s.startsWith($tag.attr('name'), 'attr_');
		//return $tag.is("[title]") && ATTR_TITLE_REGEX.test($tag.attr('title'));
	}


	// start controller

	var ADD_REPEATING_ITEM_TEMPLATE = function() {
		return _.template(
			'<div class="repitem">' +
			'	<div class="itemcontrol"><button class="btn btn-danger pictos repcontrol_del">#del</button></div>' +
			'	<%=template %>' +
			'</div>');
	};


	function addRepeatAttributeItem($templateEl, $containerEl, item) {
		var appendedRepeatingEl = $(ADD_REPEATING_ITEM_TEMPLATE()({
			template : $templateEl.html()
		}))
			.appendTo($containerEl);

		if (!item) {
			item = {};
		}

		var groupName = $containerEl.attr("data-groupname");

		$("INPUT,TEXTAREA", appendedRepeatingEl).each(function() {
			var name = $(this).attr('name');
			var newInputName = groupName + '_' + name;
			$(this).attr('name', newInputName);


			var expectedValue = item[newInputName.substr(groupName.length + "_attr_".length)];
			if (expectedValue) {
				$(this).val(expectedValue);
			}
		});

		$("SELECT", appendedRepeatingEl).each(function() {
			var name = $(this).attr('name');
			var newInputName = $containerEl.attr("data-groupname") + '_' + name;
			$(this).attr('name', newInputName);

			var expectedValue = item[newInputName.substr(groupName.length + "_attr_".length)];
			if (expectedValue) {
				$(this).val([expectedValue]);
			}
		});
	}


	// end controller

	function attachRepeatingContainer(fieldsetEl) {
		console.log('attachRepeatingContainer', fieldsetEl);

		var container = $("<DIV class='repcontainer'>");
		container.attr("data-groupname", fieldsetEl.attr('class'));

		fieldsetEl.after(container);

		container.click(function(e) {
			var $eventTarget = $(e.target);

			if ($eventTarget.hasClass("repcontrol_del")) {
				var $repItem = $eventTarget.parents(".repitem");
				$repItem.remove();

				e.stopPropagation();
				return false;
			}
		});

		return container;
	}

	function createRepeatingController($templateEl, $containerEl) {
		var container = $("<DIV>");
		var addButton = $("<BUTTON>Add</BUTTON>");
		container.append(addButton);

		addButton.click(function(e) {
			e.preventDefault();
			addRepeatAttributeItem($templateEl, $containerEl);
			return false;
		});

		return container;
	}

	function attachRepeatingController($parentEl, $containerEl) {
		console.log('attachRepeatingController', $parentEl);

		var control = createRepeatingController($parentEl, $containerEl);
		$containerEl.after(control);

		return control;
	}

	function prepareEachRepeatingElement() {
		var $repEl = $(this);
		if (!isRepeatingFieldset($repEl)) {
			return;
		}



		// this 의 innerHTML 로 템플릿을 만든다.
		// 	템플릿 만들기
		//  fieldset 숨기기

		$repEl.hide();
		var $repeatingContainer = attachRepeatingContainer($repEl);
		attachRepeatingController($repEl, $repeatingContainer);

		/*
		 - add modify 버튼 HTML 만들기
		 - add 버튼 눌렀을 때 동작 등록하기
		 - modify 버튼 눌렀을 때 동작 등록하기
		 */
	}

	/*
	 voca
	 - attribute : 시트에서 의미있는 tag
	 */

	function parseAttribute($tag) {
		var attrName = $tag.attr('name');
		attrName = attrName.substr("attr_".length);

		if ($tag.is(":checkbox")) {
			return {
				name : attrName,
				value : $tag.is(":checked") ? $tag.val() : undefined
			};
		} else {
			return {
				name : attrName,
				value : $tag.val()
			};
		}
	}

	function loopGetDataAttributeTag(attrs) {
		var $this = $(this);
		if (!isThisTagAttributeTag($this) || isRepeatingFieldset($this)) {
			return;
		}

		var attr = parseAttribute($this);

		if (!!attr.value) {
			attrs[attr.name] = attr.value;
		}
	}

	function loopGetRepeatingDataAttributes(attrs) {
		var $this = $(this);


		var datas = [];

		var groupName = $this.attr('data-groupname');
		$(".repitem", $this).each(function() {
			var eachItemData = {};

			$("INPUT,TEXTAREA,SELECT", this).each(function() {
				var itemVal = $(this).val();
				var itemName = $(this).attr('name');
				itemName = itemName.substr(groupName.length + "_attr_".length);

				eachItemData[itemName] = itemVal;
			});

			if (!_.isEmpty(eachItemData)) {
				datas.push(eachItemData);
			}
		});


		attrs[groupName.substr("repeating_".length)] = datas;
	}


	function loopSetDataToAttributeTag_repeatingData(value, key, rpgSheetElement) {
		console.log('repeating data set', key, value);

		var groupName = "repeating_" + key;

		var $containerEl = $(".repcontainer[data-groupname="+groupName+"]", rpgSheetElement);
		var $templateEl = $("FIELDSET."+groupName, rpgSheetElement);

		if ($containerEl.length < 1 || $templateEl.length < 1) {
			console.log('no repeating element');
			return;
		}

		_.each(value, function(item) {
			addRepeatAttributeItem($templateEl, $containerEl, item);
		});
	}

	function loopSetDataToAttributeTag_normalData(value, key, rpgSheetElement) {
		var selector = _.template(" [name=attr_<%=name%>]", {
			name : key
		});

		var $input = $(selector, rpgSheetElement);

		if (!$input) {
			return;
		}

		if ($input.is(":checkbox")) {
			$input.val([value]);
		} else {
			$input.val(value);
		}
	}

	$.fn.rpgSheet = function(rpgSheetData) {
		this.find("fieldset").each(prepareEachRepeatingElement);

		_.each(rpgSheetData, function (value, key) {
			if (_.isArray(value)) {
				loopSetDataToAttributeTag_repeatingData(value, key, this);
			} else {
				loopSetDataToAttributeTag_normalData(value, key, this);
			}
		}, this);

		var $rsSheet = this;

		return {
			formSubmitSupport : function () {
				var attrs = {};

				// normal elements
				$("INPUT,TEXTAREA", $rsSheet).each(function() {
					loopGetDataAttributeTag.call(this, attrs);
				});


				// loop elements
				$(".repcontainer", $rsSheet).each(function() {
					loopGetRepeatingDataAttributes.call(this, attrs);
				});

				$("INPUT[name=rs_data]").val(JSON.stringify(attrs));

				return true;
			}
		};
	};



}(
	jQuery,
	_,
	(function() {
		// utils start
		function makeString(object) {
			if (object == null) return '';
			return '' + object;
		}

		function toPositive(number) {
			return number < 0 ? 0 : (+number || 0);
		}

		return {
			startsWith : function startsWith(str, starts, position) {
				str = makeString(str);
				starts = '' + starts;
				position = position == null ? 0 : Math.min(toPositive(position), str.length);
				return str.lastIndexOf(starts, position) === position;
			}
		};
	}())
));
(function($, _) {
	// Element
	$(window).load(function() {
		var rpgSheet = $("#rs_sheet").rpgSheet(window.rsData);

		$("#post").submit(_.bind(rpgSheet.formSubmitSupport, rpgSheet));
	});
	///
}(
	jQuery,
	_
));
