function postFormButton(button)
{
	postForm(button.form);
}

function postFormField(field, e)
{
	if(e.keyCode == 13)
	{
		postForm(field.form);
		return false;
	}
	else
	{
		return true;
	}
}

function postForm(form)
{
	var formIDField = addHiddenField(form.id, "formID", form);
	form.submit();
	form.removeChild(formIDField);
}

function addHiddenField(value, name, form)
{
	var hiddenF = document.createElement("input");
	hiddenF.type = "hidden";
	hiddenF.name = name;
	hiddenF.value = value;
	form.appendChild(hiddenF);
	return hiddenF;
}