<?php
    function rw_enrich_options1($settings, $defaults)
    {
        $ret = @rw_get_default_value($settings, new stdClass());
        $ret->boost = @rw_get_default_value($settings->boost, new stdClass());
        $ret->imgUrl = @rw_get_default_value($settings->imgUrl, new stdClass());
        $ret->advanced = @rw_get_default_value($settings->advanced, new stdClass());
        $ret->advanced->star = @rw_get_default_value($settings->advanced->star, new stdClass());
        $ret->advanced->nero = @rw_get_default_value($settings->advanced->nero, new stdClass());
        $ret->advanced->font = @rw_get_default_value($settings->advanced->font, new stdClass());
        $ret->advanced->font->hover = @rw_get_default_value($settings->advanced->font->hover, new stdClass());
        $ret->advanced->layout = @rw_get_default_value($settings->advanced->layout, new stdClass());
        $ret->advanced->layout->align = @rw_get_default_value($settings->advanced->layout->align, new stdClass());
        $ret->advanced->text = @rw_get_default_value($settings->advanced->text, new stdClass());
        $ret->advanced->css = @rw_get_default_value($settings->advanced->css, new stdClass());
        
        $ret->uarid = @rw_get_default_value($settings->uarid, $defaults->uarid);
        $ret->lng = @rw_get_default_value($settings->lng, $defaults->lng);
        $ret->url = @rw_get_default_value($settings->url, $defaults->url);
        $ret->title = @rw_get_default_value($settings->title, $defaults->title);
        $ret->type = @rw_get_default_value($settings->type, $defaults->type);
        $ret->rclass = @rw_get_default_value($settings->rclass, $defaults->rclass);
        $ret->size = @rw_get_default_value($settings->size, $defaults->size);
        $ret->color = @rw_get_default_value($settings->color, $defaults->color); // deprecated
        $ret->style = @rw_get_default_value($settings->style, $defaults->style);
        $ret->imgUrl->ltr = @rw_get_default_value($settings->imgUrl->ltr, $defaults->imgUrl->ltr);
        $ret->imgUrl->rtl = @rw_get_default_value($settings->imgUrl->rtl, $defaults->imgUrl->rtl);
        $ret->readOnly = @rw_get_default_value($settings->readOnly, $defaults->readOnly);
        $ret->reVote = @rw_get_default_value($settings->reVote, $defaults->reVote);
        $ret->showInfo = @rw_get_default_value($settings->showInfo, $defaults->showInfo);
        $ret->showTooltip = @rw_get_default_value($settings->showTooltip, $defaults->showTooltip);
        $ret->beforeRate = @rw_get_default_value($settings->beforeRate, $defaults->beforeRate);
        $ret->afterRate = @rw_get_default_value($settings->afterRate, $defaults->afterRate);
        
        $ret->boost->votes = @rw_get_default_value($settings->boost->votes, $defaults->boost->votes);
        $ret->boost->rate = @rw_get_default_value($settings->boost->rate, $defaults->boost->rate);

        $ret->advanced->star->stars = @rw_get_default_value($settings->advanced->star->stars, $defaults->advanced->star->stars);

        $ret->advanced->nero->showDislike = @rw_get_default_value($settings->advanced->nero->showDislike, $defaults->advanced->nero->showDislike);
        $ret->advanced->nero->showLike = @rw_get_default_value($settings->advanced->nero->showLike, $defaults->advanced->nero->showLike);

        $ret->advanced->font->bold = @rw_get_default_value($settings->advanced->font->bold, $defaults->advanced->font->bold);
        $ret->advanced->font->italic = @rw_get_default_value($settings->advanced->font->italic, $defaults->advanced->font->italic);
        $ret->advanced->font->color = @rw_get_default_value($settings->advanced->font->color, $defaults->advanced->font->color);
        $ret->advanced->font->size = @rw_get_default_value($settings->advanced->font->size, $defaults->advanced->font->size);
        $ret->advanced->font->type = @rw_get_default_value($settings->advanced->font->type, $defaults->advanced->font->type);
        $ret->advanced->font->hover->color = @rw_get_default_value($settings->advanced->font->hover->color, $defaults->advanced->font->hover->color);

        $ret->advanced->layout->dir = @rw_get_default_value($settings->advanced->layout->dir, $defaults->advanced->layout->dir);
        $ret->advanced->layout->lineHeight = @rw_get_default_value($settings->advanced->layout->lineHeight, $defaults->advanced->layout->lineHeight);
        $ret->advanced->layout->align->hor = @rw_get_default_value($settings->advanced->layout->align->hor, $defaults->advanced->layout->align->hor);
        $ret->advanced->layout->align->ver = @rw_get_default_value($settings->advanced->layout->align->ver, $defaults->advanced->layout->align->ver);

        $ret->advanced->text->rateAwful = @rw_get_default_value($settings->advanced->text->rateAwful, $defaults->advanced->text->rateAwful);
        $ret->advanced->text->ratePoor = @rw_get_default_value($settings->advanced->text->ratePoor, $defaults->advanced->text->ratePoor);
        $ret->advanced->text->rateAverage = @rw_get_default_value($settings->advanced->text->rateAverage, $defaults->advanced->text->rateAverage);
        $ret->advanced->text->rateGood = @rw_get_default_value($settings->advanced->text->rateGood, $defaults->advanced->text->rateGood);
        $ret->advanced->text->rateExcellent = @rw_get_default_value($settings->advanced->text->rateExcellent, $defaults->advanced->text->rateExcellent);
        $ret->advanced->text->rateThis = @rw_get_default_value($settings->advanced->text->rateThis, $defaults->advanced->text->rateThis);
        $ret->advanced->text->like = @rw_get_default_value($settings->advanced->text->like, $defaults->advanced->text->like);
        $ret->advanced->text->dislike = @rw_get_default_value($settings->advanced->text->dislike, $defaults->advanced->text->dislike);
        $ret->advanced->text->vote = @rw_get_default_value($settings->advanced->text->vote, $defaults->advanced->text->vote);
        $ret->advanced->text->votes = @rw_get_default_value($settings->advanced->text->votes, $defaults->advanced->text->votes);
        $ret->advanced->text->thanks = @rw_get_default_value($settings->advanced->text->thanks, $defaults->advanced->text->thanks);
        
        $ret->advanced->css->container = @rw_get_default_value($settings->advanced->css->container, $defaults->advanced->css->container);
        
        return $ret;
    }
    
    function rw_set_language_options(&$settings, $dictionary = array(), $dir = "ltr", $hor = "right")
    {
        $settings = @rw_get_default_value($settings, new stdClass());
        $settings->advanced = @rw_get_default_value($settings->advanced, new stdClass());
        $settings->advanced->text = @rw_get_default_value($settings->advanced->text, new stdClass());
        $settings->advanced->layout = @rw_get_default_value($settings->advanced->layout, new stdClass());
        $settings->advanced->layout->align = @rw_get_default_value($settings->advanced->layout->align, new stdClass());

        $settings->advanced->layout->dir = @rw_get_default_value($settings->advanced->layout->dir, $dir, DUMMY_STR);
        $settings->advanced->layout->align->hor = @rw_get_default_value($settings->advanced->layout->align->hor, $hor, DUMMY_STR);

        $settings->advanced->text->rateAwful = @rw_get_default_value($settings->advanced->text->rateAwful, $dictionary["rateAwful"], DUMMY_STR);
        $settings->advanced->text->ratePoor = @rw_get_default_value($settings->advanced->text->ratePoor, $dictionary["ratePoor"], DUMMY_STR);
        $settings->advanced->text->rateAverage = @rw_get_default_value($settings->advanced->text->rateAverage, $dictionary["rateAverage"], DUMMY_STR);
        $settings->advanced->text->rateGood = @rw_get_default_value($settings->advanced->text->rateGood, $dictionary["rateGood"], DUMMY_STR);
        $settings->advanced->text->rateExcellent = @rw_get_default_value($settings->advanced->text->rateExcellent, $dictionary["rateExcellent"], DUMMY_STR);
        $settings->advanced->text->rateThis = @rw_get_default_value($settings->advanced->text->rateThis, $dictionary["rateThis"], DUMMY_STR);
        $settings->advanced->text->like = @rw_get_default_value($settings->advanced->text->like, $dictionary["like"], DUMMY_STR);
        $settings->advanced->text->dislike = @rw_get_default_value($settings->advanced->text->dislike, $dictionary["dislike"], DUMMY_STR);
        $settings->advanced->text->vote = @rw_get_default_value($settings->advanced->text->vote, $dictionary["vote"], DUMMY_STR);
        $settings->advanced->text->votes = @rw_get_default_value($settings->advanced->text->votes, $dictionary["votes"], DUMMY_STR);
        $settings->advanced->text->thanks = @rw_get_default_value($settings->advanced->text->thanks, $dictionary["thanks"], DUMMY_STR);
    }
    
    function rw_enrich_options(&$settings, $dictionary = array(), $dir = "ltr", $hor = "right", $type = "star")
    {
        $settings = @rw_get_default_value($settings, new stdClass());
        $settings->boost = @rw_get_default_value($settings->boost, new stdClass());
        $settings->advanced = @rw_get_default_value($settings->advanced, new stdClass());
        $settings->advanced->font = @rw_get_default_value($settings->advanced->font, new stdClass());
        $settings->advanced->font->hover = @rw_get_default_value($settings->advanced->font->hover, new stdClass());
        $settings->advanced->layout = @rw_get_default_value($settings->advanced->layout, new stdClass());
        $settings->advanced->layout->align = @rw_get_default_value($settings->advanced->layout->align, new stdClass());
        $settings->advanced->text = @rw_get_default_value($settings->advanced->text, new stdClass());
        
        $settings->lng = @rw_get_default_value($settings->lng, "en");
        $settings->url = @rw_get_default_value($settings->url, "");
        $settings->title = @rw_get_default_value($settings->title, "");
        $settings->type = @rw_get_default_value($settings->type, $type);
        $settings->rclass = @rw_get_default_value($settings->rclass, "");
        $settings->size = @rw_get_default_value($settings->size, "small");
        $settings->color = @rw_get_default_value($settings->color, "yellow");
        $settings->style = @rw_get_default_value($settings->style, "oxygen");
        $settings->imgUrl = @rw_get_default_value($settings->imgUrl, "");
        $settings->readOnly = @rw_get_default_value($settings->readOnly, false);
        $settings->showInfo = @rw_get_default_value($settings->showInfo, true);
        $settings->showTooltip = @rw_get_default_value($settings->showTooltip, true);
        $settings->beforeRate = @rw_get_default_value($settings->beforeRate, null);
        $settings->afterRate = @rw_get_default_value($settings->beforeRate, null);
        
        $settings->boost->votes = @rw_get_default_value($settings->boost->votes, 0);
        $settings->boost->rate = @rw_get_default_value($settings->boost->rate, 5);

        $settings->advanced->font->bold = @rw_get_default_value($settings->advanced->font->bold, false);
        $settings->advanced->font->italic = @rw_get_default_value($settings->advanced->font->italic, false);
        $settings->advanced->font->color = @rw_get_default_value($settings->advanced->font->color, "#000");
        $settings->advanced->font->size = @rw_get_default_value($settings->advanced->font->size, "12px");
        $settings->advanced->font->type = @rw_get_default_value($settings->advanced->font->type, "inherit");
        $settings->advanced->font->hover->color = @rw_get_default_value($settings->advanced->font->hover->color, "#000");

        $settings->advanced->layout->dir = @rw_get_default_value($settings->advanced->layout->dir, $dir);
        $settings->advanced->layout->lineHeight = @rw_get_default_value($settings->advanced->layout->lineHeight, "18px");
        $settings->advanced->layout->align->hor = @rw_get_default_value($settings->advanced->layout->align->hor, $hor);
        $settings->advanced->layout->align->ver = @rw_get_default_value($settings->advanced->layout->align->ver, "middle");

        $settings->advanced->text->rateAwful = @rw_get_default_value($settings->advanced->text->rateAwful, $dictionary["rateAwful"]);
        $settings->advanced->text->ratePoor = @rw_get_default_value($settings->advanced->text->ratePoor, $dictionary["ratePoor"]);
        $settings->advanced->text->rateAverage = @rw_get_default_value($settings->advanced->text->rateAverage, $dictionary["rateAverage"]);
        $settings->advanced->text->rateGood = @rw_get_default_value($settings->advanced->text->rateGood, $dictionary["rateGood"]);
        $settings->advanced->text->rateExcellent = @rw_get_default_value($settings->advanced->text->rateExcellent, $dictionary["rateExcellent"]);
        $settings->advanced->text->rateThis = @rw_get_default_value($settings->advanced->text->rateThis, $dictionary["rateThis"]);
        $settings->advanced->text->like = @rw_get_default_value($settings->advanced->text->like, $dictionary["like"]);
        $settings->advanced->text->dislike = @rw_get_default_value($settings->advanced->text->dislike, $dictionary["dislike"]);
        $settings->advanced->text->vote = @rw_get_default_value($settings->advanced->text->vote, $dictionary["vote"]);
        $settings->advanced->text->votes = @rw_get_default_value($settings->advanced->text->votes, $dictionary["votes"]);
        $settings->advanced->text->thanks = @rw_get_default_value($settings->advanced->text->thanks, $dictionary["thanks"]);
    }
        
    function rw_get_default_value($val, $def, $null = null)
    {
        return ((isset($val) && $val !== $null) ? $val : $def);
    }
?>
