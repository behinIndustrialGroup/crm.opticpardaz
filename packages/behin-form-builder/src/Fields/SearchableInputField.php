<?php

namespace MyFormBuilder\Fields;

class SearchableInputField extends AbstractField
{
    public function render(): string
    {
        $id = $this->attributes['id'] ?? $this->name;
        $containerId = $id . '_searchable_container';
        $hiddenId = $id . '_value';
        $searchInputId = $id . '_search';
        $resultsId = $id . '_results';
        $endpoint = $this->attributes['endpoint'] ?? '';
        $minChars = isset($this->attributes['minChars']) ? (int)$this->attributes['minChars'] : 3;
        $limit = isset($this->attributes['limit']) && $this->attributes['limit'] !== ''
            ? (int)$this->attributes['limit']
            : null;
        $value = $this->attributes['value'] ?? '';
        $initialLabel = $this->attributes['initial_label'] ?? ($this->attributes['initialLabel'] ?? '');
        $placeholder = $this->attributes['placeholder'] ?? '';
        $readonly = $this->attributes['readonly'] ?? '';
        $required = $this->attributes['required'] ?? '';

        $label = trans('fields.' . $this->name);
        $requiredMark = $required === 'on' && $readonly !== 'on' ? ' <span class="text-danger">*</span>' : '';
        $readonlyAttribute = $readonly === 'on' ? 'readonly' : '';
        $placeholderAttribute = $placeholder ? 'placeholder="' . htmlspecialchars($placeholder, ENT_QUOTES) . '"' : '';
        $requiredAttribute = $required === 'on' ? 'required' : '';
        $initialLabelAttribute = htmlspecialchars($initialLabel, ENT_QUOTES);
        $endpointAttribute = htmlspecialchars($endpoint, ENT_QUOTES);
        $valueAttribute = htmlspecialchars((string)$value, ENT_QUOTES);

        $config = json_encode([
            'containerId' => $containerId,
            'hiddenId' => $hiddenId,
            'inputId' => $searchInputId,
            'resultsId' => $resultsId,
            'endpoint' => $endpoint,
            'minChars' => $minChars,
            'limit' => $limit,
            'initialLabel' => $initialLabel,
            'fieldName' => $this->name,
        ]);

        $s = '<div class="form-group position-relative" id="' . $containerId . '">';
        $s .= '<label for="' . $searchInputId . '">' . $label . $requiredMark . '</label>';
        $s .= '<input type="hidden" name="' . $this->name . '" id="' . $hiddenId . '" value="' . $valueAttribute . '" ' . $requiredAttribute . ' data-role="searchable-value">';
        $s .= '<input type="text" class="form-control" id="' . $searchInputId . '" autocomplete="off" data-role="searchable-input" ';
        $s .= 'data-endpoint="' . $endpointAttribute . '" data-min-chars="' . $minChars . '" data-limit="' . ($limit ?? '') . '" ';
        $s .= 'data-initial-label="' . $initialLabelAttribute . '" ' . $placeholderAttribute . ' ' . $readonlyAttribute . '> ';
        $s .= '<div class="list-group" id="' . $resultsId . '" style="position:absolute; width:100%; z-index:1050;"></div>';
        $s .= '</div>';

        $s .= '<script>(function(){';
        $s .= 'const config = ' . $config . ';';
        $s .= 'const container = document.getElementById(config.containerId);';
        $s .= 'if(!container){return;}';
        $s .= 'const searchInput = document.getElementById(config.inputId);';
        $s .= 'const hiddenInput = document.getElementById(config.hiddenId);';
        $s .= 'const resultsBox = document.getElementById(config.resultsId);';
        $s .= 'if(!searchInput || !hiddenInput || !resultsBox){return;}';
        $s .= 'window.formBuilderSearchableFields = window.formBuilderSearchableFields || {};';
        $s .= 'const state = { timer:null, controller:null };';
        $s .= 'function clearResults(){resultsBox.innerHTML="";}';
        $s .= 'function selectItem(item){hiddenInput.value=item.id ?? "";searchInput.value=item.label ?? "";clearResults();}';
        $s .= 'function buildUrl(params){try{const url=new URL(config.endpoint, window.location.origin);Object.keys(params).forEach(function(key){if(params[key]!==undefined && params[key]!==null && params[key]!=="" ){url.searchParams.set(key, params[key]);}});if(config.limit && params.term){url.searchParams.set("limit", config.limit);}return url.toString();}catch(e){return null;}}';
        $s .= 'function fetchResults(params, isInitial){const url=buildUrl(params);if(!url){return;}if(state.controller){state.controller.abort();}state.controller=new AbortController();fetch(url,{headers:{"Accept":"application/json"},signal:state.controller.signal}).then(function(response){return response.json ? response.json() : [];}).then(function(data){if(!Array.isArray(data)){return;}
        if (isInitial) {
            if (Array.isArray(data) && hiddenInput.value) {
                const exact = data.find(item => item.id == hiddenInput.value);
                if (exact) {
                    selectItem(exact);
                }
            }
            return;
        }
        const items=config.limit?data.slice(0, config.limit):data;renderResults(items);}).catch(function(error){if(error.name!=="AbortError"){console.error(error);}});};';
        $s .= 'function renderResults(items){clearResults();if(!items.length){return;}items.forEach(function(item){const option=document.createElement("button");option.setAttribute("type","button");option.className="list-group-item list-group-item-action";option.textContent=item.label ?? item.id;option.addEventListener("click",function(){selectItem(item);});resultsBox.appendChild(option);});}';
        $s .= 'function handleInput(){const term=searchInput.value.trim();if(term.length < config.minChars){clearResults();return;}if(state.timer){clearTimeout(state.timer);}state.timer=setTimeout(function(){fetchResults({term:term});},300);}';
        $s .= 'searchInput.addEventListener("input", handleInput);';
        $s .= 'searchInput.addEventListener("focus", function(){const term=searchInput.value.trim();if(term.length >= config.minChars){handleInput();}});';
        $s .= 'document.addEventListener("click", function(event){if(!container.contains(event.target)){clearResults();}});';
        $s .= 'if(hiddenInput.value){if(config.initialLabel){selectItem({id:hiddenInput.value,label:config.initialLabel});}else if(config.endpoint){fetchResults({id:hiddenInput.value}, true);} }';
        $s .= 'const api={getValue:function(){return hiddenInput.value;},setValue:function(item){if(item && typeof item==="object" && item.id!==undefined && item.label!==undefined){selectItem(item);}else if(item===null || item===undefined){selectItem({id:"",label:""});}}};';
        $s .= 'container.searchableField = api;';
        $s .= 'window.formBuilderSearchableFields[config.fieldName]=api;';
        $s .= '})();</script>';

        return $s;
    }

    public function getValue(): mixed
    {
        return $this->attributes['value'] ?? null;
    }

    public function setValue($value): void
    {
        $this->attributes['value'] = $value;
    }
}
