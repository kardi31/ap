<?php

/**
 * XML_AdminController
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Xml_AdminController extends MF_Controller_Action {
    
    public function generateSitemapAction() {
        $categoryService = $this->_service->getService('Product_Service_Category');
        $productService = $this->_service->getService('Product_Service_Product');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $newsService = $this->_service->getService('News_Service_News');
        $locationService = $this->_service->getService('Location_Service_Location');
        $languageList = $i18nService->getLanguageListForSiteMap();

        $categories = $categoryService->getAllCategoriesForSiteMap(Doctrine_Core::HYDRATE_ARRAY);
        $products = $productService->getAllProductForSiteMap(Doctrine_Core::HYDRATE_ARRAY);
        $news = $newsService->getAllNewsForSiteMap(Doctrine_Core::HYDRATE_ARRAY);
        $locations = $locationService->getAllLocationsForSiteMap(Doctrine_Core::HYDRATE_ARRAY);
        
        $numberOfProductsPerPageCategory = $categoryService->getProductCountPerPage();
        $numberOfNewsPerPage = $newsService->getArticleItemCountPerPage();
        
        
        $file = fopen(APPLICATION_PATH."/../public/sitemap/sitemap.xml", 'w+');
        $time = date("Y-m-d")."T".date("H:i:s+00:00");

        $domain = "http://bodyempire.pl/";
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
            <urlset
                  xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
                  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                  xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
                        http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
               '; 
               foreach($languageList as $lang):
                   if ($lang == "en"):
                    $xml .= '<url>
                    <loc>'.$domain.$lang.'/page/about-us</loc>
                         <lastmod>'.$time.'</lastmod>
                    </url>
                    ';  
                    $xml .= '<url>
                    <loc>'.$domain.$lang.'/page/prof-andrzej-frydrychowski-md-ph-d</loc>
                         <lastmod>'.$time.'</lastmod>
                    </url>
                    ';  
                    $xml .= '<url>
                    <loc>'.$domain.$lang.'/contact</loc>
                         <lastmod>'.$time.'</lastmod>
                    </url>
                    ';  
                    $xml .= '<url>
                    <loc>'.$domain.$lang.'/login</loc>
                         <lastmod>'.$time.'</lastmod>
                    </url>
                    '; 
                    $xml .= '<url>
                    <loc>'.$domain.$lang.'/register</loc>
                         <lastmod>'.$time.'</lastmod>
                    </url>
                    '; 
                    $xml .= '<url>
                    <loc>'.$domain.$lang.'/cart</loc>
                         <lastmod>'.$time.'</lastmod>
                    </url>
                    '; 
                   endif; 

                   foreach($categories as $category):
                        if(count($category['Products'])%$numberOfProductsPerPageCategory != 0):
                            $categoryPage = (int)(count($category['Products'])/$numberOfProductsPerPageCategory) + 1;
                        else:
                            $categoryPage = count($category['Products'])/$numberOfProductsPerPageCategory;
                        endif;
                        for($i=1;$i<=$categoryPage;$i++):
                            if($i==1):
                                $xml .= '<url>
                                <loc>'.$domain.$lang.'/'.$category['Translation'][$lang]['slug'].'</loc>
                                    <lastmod>'.$time.'</lastmod>
                                </url>
                                ';  
                            else:
                                 $xml .= '<url>
                                <loc>'.$domain.$lang.'/'.$category['Translation'][$lang]['slug'].'/'.$i.'</loc>
                                    <lastmod>'.$time.'</lastmod>
                                </url>
                                ';    
                            endif;
                        endfor;
                   endforeach;
                   foreach($products as $product):
                        $xml .= '<url>
                        <loc>'.$domain.$lang.'/'.$product['Categories'][0]['Translation'][$lang]['slug'].'/'.$product['Translation'][$lang]['slug'].'</loc>
                            <lastmod>'.$time.'</lastmod>
                        </url>
                        ';  
                   endforeach;
                   if(count($news)%$numberOfNewsPerPage != 0):
                        $newsPage = (int)(count($news)/$numberOfNewsPerPage) + 1;
                   else:
                        $newsPage = count($news)/$numberOfNewsPerPage;
                   endif;
                   if ($lang == "en"):
                        for($i=1;$i<=$newsPage;$i++):
                             if($i==1):
                                 $xml .= '<url>
                                 <loc>'.$domain.$lang.'/news</loc>
                                     <lastmod>'.$time.'</lastmod>
                                 </url>
                                 ';  
                             else:
                                 $xml .= '<url>
                                 <loc>'.$domain.$lang.'/news/'.$i.'</loc>
                                     <lastmod>'.$time.'</lastmod>
                                 </url>
                                 ';    
                             endif;
                        endfor;
                        foreach($news as $newsItem):
                             $xml .= '<url>
                             <loc>'.$domain.$lang.'/news/'.$newsItem['Translation'][$lang]['slug'].'</loc>
                                 <lastmod>'.$time.'</lastmod>
                             </url>
                             ';  
                        endforeach;
                    endif;
                    if ($lang == "en"):
                        $xml .= '<url>
                        <loc>'.$domain.$lang.'/faq</loc>
                             <lastmod>'.$time.'</lastmod>
                        </url>
                        '; 
                        $xml .= '<url>
                        <loc>'.$domain.$lang.'/faq/psoriasis</loc>
                             <lastmod>'.$time.'</lastmod>
                        </url>
                        '; 
                        $xml .= '<url>
                        <loc>'.$domain.$lang.'/faq/rosacea</loc>
                             <lastmod>'.$time.'</lastmod>
                        </url>
                        '; 
                        $xml .= '<url>
                        <loc>'.$domain.$lang.'/faq/collagen</loc>
                             <lastmod>'.$time.'</lastmod>
                        </url>
                        '; 
                    endif;
                    if ($lang == "en"):
                        $xml .= '<url>
                        <loc>'.$domain.$lang.'/location</loc>
                             <lastmod>'.$time.'</lastmod>
                        </url>
                        '; 
                        foreach($locations as $location):
                            $xml .= '<url>
                            <loc>'.$domain.$lang.'/location/'.$location['Translation'][$lang]['slug'].'</loc>
                                <lastmod>'.$time.'</lastmod>
                            </url>
                            ';  
                       endforeach;
                   endif;
               endforeach;
                 

        $xml .='</urlset>';
   
        fwrite($file, $xml);
        fclose($file);
    }
    
    public function generateSitemapAjurwedaAction() {
        $menuService = $this->_service->getService('Menu_Service_Menu');
        $articleService = $this->_service->getService('Article_Service_Article');
        $newsService = $this->_service->getService('News_Service_News');
        $eventService = $this->_service->getService('Event_Service_Event');
        $eventPromotedService = $this->_service->getService('Event_Service_EventPromoted');
        $basicActivityService = $this->_service->getService('Company_Service_BasicActivity');
        $companyService = $this->_service->getService('Company_Service_Company');
        $bookService = $this->_service->getService('Book_Service_Book');
        $workshopService = $this->_service->getService('Workshop_Service_Workshop');
        $reviewService = $this->_service->getService('Review_Service_Review');
        $partnerService = $this->_service->getService('Partner_Service_Partner');
        $categoriesCookingService = $this->_service->getService('Cooking_Service_Category');
        $recipeService = $this->_service->getService('Cooking_Service_Recipe');
        $trainerService = $this->_service->getService('Trainer_Service_Trainer');
        $trainerPromotedService = $this->_service->getService('Trainer_Service_TrainerPromoted');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $languageList = $i18nService->getLanguageListForSiteMap();
        
        $menu = $menuService->getMenu(1);
        if($menu):
            $menuTree = $menuService->getMenuItemTreeForSiteMap($menu);
        endif;
        $news = $newsService->getAllNewsForSiteMap(Doctrine_Core::HYDRATE_ARRAY);
        $eventsNormal = $eventService->getAllEventsForSiteMap(Doctrine_Core::HYDRATE_ARRAY);
        $eventsPromoted = $eventPromotedService->getAllEventsPromotedForSiteMap(Doctrine_Core::HYDRATE_ARRAY);
        $events = array_merge($eventsPromoted, $eventsNormal);
        $basicActivities = $basicActivityService->getAllBasicActivitiesForSiteMap(Doctrine_Core::HYDRATE_ARRAY);
        $companies = $companyService->getAllCompaniesForSiteMap(Doctrine_Core::HYDRATE_ARRAY);
        $books = $bookService->getAllBooksForSiteMap(Doctrine_Core::HYDRATE_ARRAY);
        $workshops = $workshopService->getAllWorkshopsForSiteMap(Doctrine_Core::HYDRATE_ARRAY);
        $reviews = $reviewService->getAllReviewsForSiteMap(Doctrine_Core::HYDRATE_ARRAY);
        $partners = $partnerService->getAllPartnersForSiteMap(Doctrine_Core::HYDRATE_ARRAY);
        $categoriesCooking = $categoriesCookingService->getAllCategoriesForSiteMap(Doctrine_Core::HYDRATE_ARRAY);
        $recipes = $recipeService->getAllRecipesForSiteMap(Doctrine_Core::HYDRATE_ARRAY);
        $trainersNormal = $trainerService->getAllTrainersForSiteMap(Doctrine_Core::HYDRATE_ARRAY);
        $trainersPromoted = $trainerPromotedService->getAllTrainersPromotedForSiteMap(Doctrine_Core::HYDRATE_ARRAY);
        $trainers = array_merge($trainersPromoted, $trainersNormal);
        
//        $numberOfNewestProduct = $productService->getNumberOfNewestProductForSiteMap(Doctrine_Core::HYDRATE_ARRAY);
//        $numberOfNewestProduct = $numberOfNewestProduct[0]['number_of_products'];
//        $numberOfPromotionProduct = $productService->getNumberOfPromotionProductForSiteMap(Doctrine_Core::HYDRATE_ARRAY);
//        $numberOfPromotionProduct = $numberOfPromotionProduct[0]['number_of_products'];
//        $numberOfReducedPriceProduct = $productService->getNumberOfReducedPriceProductForSiteMap(Doctrine_Core::HYDRATE_ARRAY);
//        $numberOfReducedPriceProduct = $numberOfReducedPriceProduct[0]['number_of_products'];
//        $numberOfAyurvedaProduct = $productService->getNumberOfAyurvedaProductForSiteMap(Doctrine_Core::HYDRATE_ARRAY);
//        $numberOfAyurvedaProduct = $numberOfAyurvedaProduct[0]['number_of_products'];
        
           
        $numberOfArticleItemPerPage = $articleService->getArticleItemCountPerPage();
        $numberOfNewsPerPage = $newsService->getArticleItemCountPerPage();
        $numberOfEventsPerPage = $eventService->getArticleItemCountPerPage();
        $numberOfCompaniesCategoryPerPage = $companyService->getCompaniesCategoryCountPerPage();
        $numberOfCompaniesPerPage = $companyService->getCompaniesCountPerPage();
        $numberOfBooksPerPage = $bookService->getBookItemCountPerPage();
        $numberOfWorkshopsPerPage = $workshopService->getWorkshopItemCountPerPage();
        $numberOfReviewsPerPage = $reviewService->getReviewItemCountPerPage();
        $numberOfPartnersPerPage = $partnerService->getPartnerItemCountPerPage();
        $numberOfRecipesPerPage = $recipeService->getRecipesCountPerPage();
        $numberOfTrainersPerPage = $trainerService->getTrainersCountPerPage();
        
        $file = fopen(APPLICATION_PATH."/../public/sitemaps/sitemap-ajurweda.xml", 'w+');
        $time = date("Y-m-d")."T".date("H:i:s+00:00");

        $domain = "http://a-ajurweda.pl/";
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
            <urlset
                  xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
                  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                  xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
                        http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
               '; 
               foreach($languageList as $lang):
                   $xml .= '<url>
                   <loc>'.$domain.$lang.'</loc>
                        <lastmod>'.$time.'</lastmod>
                   </url>
                   ';
                   $xml .= '<url>
                   <loc>'.$domain.$lang.'/strona/visit-us</loc>
                        <lastmod>'.$time.'</lastmod>
                   </url>
                   ';
                   $xml .= '<url>
                   <loc>'.$domain.$lang.'/program-partnerski-ajurweda</loc>
                        <lastmod>'.$time.'</lastmod>
                   </url>
                   ';
                   $xml .= '<url>
                   <loc>'.$domain.$lang.'/newsletter/register</loc>
                        <lastmod>'.$time.'</lastmod>
                   </url>
                   ';
                   $xml .= '<url>
                   <loc>'.$domain.$lang.'/register</loc>
                        <lastmod>'.$time.'</lastmod>
                   </url>
                   ';
                   $xml .= '<url>
                   <loc>'.$domain.$lang.'/login</loc>
                        <lastmod>'.$time.'</lastmod>
                   </url>
                   ';
                   $xml .= '<url>
                   <loc>'.$domain.$lang.'/profil/firma</loc>
                        <lastmod>'.$time.'</lastmod>
                   </url>
                   ';
                   $xml .= '<url>
                   <loc>'.$domain.$lang.'/profil/wydarzenia</loc>
                        <lastmod>'.$time.'</lastmod>
                   </url>
                   ';
                   $xml .= '<url>
                   <loc>'.$domain.$lang.'/profil/ustawienia</loc>
                        <lastmod>'.$time.'</lastmod>
                   </url>
                   ';
                   $xml .= '<url>
                   <loc>'.$domain.$lang.'/pomoc</loc>
                        <lastmod>'.$time.'</lastmod>
                   </url>
                   ';
                   $xml .= '<url>
                   <loc>'.$domain.$lang.'/strona/condition</loc>
                        <lastmod>'.$time.'</lastmod>
                   </url>
                   ';
                   $xml .= '<url>
                   <loc>'.$domain.$lang.'/strona/privacy-policy</loc>
                        <lastmod>'.$time.'</lastmod>
                   </url>
                   ';
                   $xml .= '<url>
                   <loc>'.$domain.$lang.'/kontakt</loc>
                        <lastmod>'.$time.'</lastmod>
                   </url>
                   ';
                   $xml .= '<url>
                   <loc>'.$domain.$lang.'/results-searching</loc>
                        <lastmod>'.$time.'</lastmod>
                   </url>
                   ';
                   
                   foreach($menuTree as $item):
                        if($item['level'] == 1 && $item->Translation[$lang]->title):
                            if(count($item['Articles'])%$numberOfArticleItemPerPage != 0):
                                $articlePage = (int)(count($item['Articles'])/$numberOfArticleItemPerPage) + 1;
                            else:
                                $articlePage = count($item['Articles'])/$numberOfArticleItemPerPage;
                            endif;
                            if($item['target_type'] && count($item['Articles']) > 0):
                                if(count($item['Articles']) < 2 && $item->Translation[$lang]->slug):
                                    $xml .= '<url>
                                    <loc>'.$domain.$lang.'/'.$item['Translation'][$lang]['slug'].'</loc>
                                        <lastmod>'.$time.'</lastmod>
                                    </url>
                                    ';  
                                else:
                                    for($i=1;$i<=$articlePage;$i++):
                                        if($i==1):
                                            $xml .= '<url>
                                            <loc>'.$domain.$lang.'/kategoria/'.$item['Translation'][$lang]['slug'].'</loc>
                                                <lastmod>'.$time.'</lastmod>
                                            </url>
                                            ';  
                                        else:
                                            $xml .= '<url>
                                            <loc>'.$domain.$lang.'/kategoria/'.$item['Translation'][$lang]['slug'].'/'.$i.'</loc>
                                                <lastmod>'.$time.'</lastmod>
                                            </url>
                                            ';  
                                        endif;
                                    endfor;
                                endif; 
                            endif;
                            if($item->getNode()->getChildren()):
                                $subtree = $item->getNode()->getChildren();
                                  foreach($subtree as $item):
                                    if($item->Translation[$lang]->title):
                                        if(count($item['Articles'])%$numberOfArticleItemPerPage != 0):
                                            $articlePage = (int)(count($item['Articles'])/$numberOfArticleItemPerPage) + 1;
                                        else:
                                            $articlePage = count($item['Articles'])/$numberOfArticleItemPerPage;
                                        endif;
                                        if($item['target_type'] && count($item['Articles']) > 0):
                                            if(count($item['Articles']) < 2 && $item->Translation[$lang]->slug):
                                                $xml .= '<url>
                                                <loc>'.$domain.$lang.'/'.$item['Translation'][$lang]['slug'].'</loc>
                                                    <lastmod>'.$time.'</lastmod>
                                                </url>
                                                ';  
                                            else:
                                                for($i=1;$i<=$articlePage;$i++):
                                                    if($i==1):
                                                        $xml .= '<url>
                                                        <loc>'.$domain.$lang.'/kategoria/'.$item['Translation'][$lang]['slug'].'</loc>
                                                            <lastmod>'.$time.'</lastmod>
                                                        </url>
                                                        ';  
                                                    else:
                                                        $xml .= '<url>
                                                        <loc>'.$domain.$lang.'/kategoria/'.$item['Translation'][$lang]['slug'].'/'.$i.'</loc>
                                                            <lastmod>'.$time.'</lastmod>
                                                        </url>
                                                        ';  
                                                    endif;
                                                endfor;
                                            endif;
                                       endif;
                                    endif;
                                endforeach;
                            endif;
                        endif;
                   endforeach;
                   if(count($news)%$numberOfNewsPerPage != 0):
                        $newsPage = (int)(count($news)/$numberOfNewsPerPage) + 1;
                   else:
                        $newsPage = count($news)/$numberOfNewsPerPage;
                   endif;
                   for($i=1;$i<=$newsPage;$i++):
                        if($i==1):
                            $xml .= '<url>
                            <loc>'.$domain.$lang.'/aktualnosci</loc>
                                <lastmod>'.$time.'</lastmod>
                            </url>
                            ';  
                        else:
                            $xml .= '<url>
                            <loc>'.$domain.$lang.'/aktualnosci/'.$i.'</loc>
                                <lastmod>'.$time.'</lastmod>
                            </url>
                            ';    
                        endif;
                   endfor;
                   foreach($news as $newsItem):
                        $xml .= '<url>
                        <loc>'.$domain.$lang.'/aktualnosci/'.$newsItem['Translation'][$lang]['slug'].'</loc>
                            <lastmod>'.$time.'</lastmod>
                        </url>
                        ';  
                   endforeach;
                   if(count($events)%$numberOfEventsPerPage != 0):
                        $eventPage = (int)(count($events)/$numberOfEventsPerPage) + 1;
                   else:
                        $eventPage = count($events)/$numberOfEventsPerPage;
                   endif;
                   for($i=1;$i<=$eventPage;$i++):
                        if($i==1):
                            $xml .= '<url>
                            <loc>'.$domain.$lang.'/wydarzenia</loc>
                                <lastmod>'.$time.'</lastmod>
                            </url>
                            ';  
                        else:
                            $xml .= '<url>
                            <loc>'.$domain.$lang.'/wydarzenia/'.$i.'</loc>
                                <lastmod>'.$time.'</lastmod>
                            </url>
                            ';    
                        endif;
                   endfor;
                   foreach($events as $event):
                       if($event['type'] == "promoted"):
                            $xml .= '<url>
                            <loc>'.$domain.$lang.'/wydarzenia/'.$event['Translation'][$lang]['slug'].'</loc>
                                 <lastmod>'.$time.'</lastmod>
                            </url>
                            ';   
                       endif;
                   endforeach;
                   if(count($trainers)%$numberOfTrainersPerPage != 0):
                        $trainerPage = (int)(count($trainers)/$numberOfTrainersPerPage) + 1;
                   else:
                        $trainerPage = count($trainers)/$numberOfTrainersPerPage;
                   endif;
                   for($i=1;$i<=$trainerPage;$i++):
                        if($i==1):
                            $xml .= '<url>
                            <loc>'.$domain.$lang.'/sylwetki</loc>
                                <lastmod>'.$time.'</lastmod>
                            </url>
                            ';  
                        else:
                            $xml .= '<url>
                            <loc>'.$domain.$lang.'/sylwetki/'.$i.'</loc>
                                <lastmod>'.$time.'</lastmod>
                            </url>
                            ';    
                        endif;
                   endfor;
                   foreach($trainers as $trainer):
                       if($trainer['type'] == "promoted"):
                            $xml .= '<url>
                            <loc>'.$domain.$lang.'/sylwetka/promowana/'.$trainer['Translation'][$lang]['slug'].'</loc>
                                 <lastmod>'.$time.'</lastmod>
                            </url>
                            ';   
                       elseif($trainer['type'] == "normal"):
                            $xml .= '<url>
                            <loc>'.$domain.$lang.'/sylwetka/zwykla/'.$trainer['Translation'][$lang]['slug'].'</loc>
                                 <lastmod>'.$time.'</lastmod>
                            </url>
                            ';   
                       endif;
                   endforeach;
                   foreach($basicActivities as $basicActivity):
                        if($basicActivity['companies_count']%$numberOfCompaniesCategoryPerPage != 0):
                            $basicActivityPage = (int)($basicActivity['companies_count']/$numberOfCompaniesCategoryPerPage) + 1;
                        else:
                            $basicActivityPage = $basicActivity['companies_count']/$numberOfCompaniesCategoryPerPage;
                        endif;
                        for($i=1;$i<=$basicActivityPage;$i++):
                            if($i==1):
                                $xml .= '<url>
                                <loc>'.$domain.$lang.'/'.$basicActivity['Translation'][$lang]['slug'].'/firmy</loc>
                                    <lastmod>'.$time.'</lastmod>
                                </url>
                                ';  
                            else:
                                 $xml .= '<url>
                                <loc>'.$domain.$lang.'/'.$basicActivity['Translation'][$lang]['slug'].'/firmy/'.$i.'</loc>
                                    <lastmod>'.$time.'</lastmod>
                                </url>
                                ';    
                            endif;
                        endfor;
                   endforeach;
                   if(count($companies)%$numberOfCompaniesPerPage != 0):
                        $companyPage = (int)(count($companies)/$numberOfCompaniesPerPage) + 1;
                   else:
                        $companyPage = count($companies)/$numberOfCompaniesPerPage;
                   endif;
                   for($i=1;$i<=$companyPage;$i++):
                        if($i==1):
                            $xml .= '<url>
                            <loc>'.$domain.$lang.'/katalog-firm</loc>
                                <lastmod>'.$time.'</lastmod>
                            </url>
                            ';  
                        else:
                            $xml .= '<url>
                            <loc>'.$domain.$lang.'/katalog-firm/'.$i.'</loc>
                                <lastmod>'.$time.'</lastmod>
                            </url>
                            ';    
                        endif;
                   endfor;
                   foreach($companies as $company):
                       if($company['BusinessCard']['type'] == 'premium'):
                            $xml .= '<url>
                            <loc>'.$domain.$lang.'/'.$company['BasicActivity']['Translation'][$lang]['slug'].'/firma/'.$company['Translation'][$lang]['slug'].'</loc>
                                <lastmod>'.$time.'</lastmod>
                            </url>
                            '; 
                       endif;
                   endforeach;
                   $xml .= '<url>
                   <loc>'.$domain.$lang.'/katalog-firm-mapa</loc>
                        <lastmod>'.$time.'</lastmod>
                   </url>
                   ';
                   $xml .= '<url>
                   <loc>'.$domain.$lang.'/profil/add-company</loc>
                        <lastmod>'.$time.'</lastmod>
                   </url>
                   ';
                   $xml .= '<url>
                   <loc>'.$domain.$lang.'/profil/edit-company</loc>
                        <lastmod>'.$time.'</lastmod>
                   </url>
                   ';
                   $xml .= '<url>
                   <loc>'.$domain.$lang.'/company-searching</loc>
                        <lastmod>'.$time.'</lastmod>
                   </url>
                   ';
                   if(count($books)%$numberOfBooksPerPage != 0):
                        $bookPage = (int)(count($books)/$numberOfBooksPerPage) + 1;
                   else:
                        $bookPage = count($books)/$numberOfBooksPerPage;
                   endif;
                   for($i=1;$i<=$bookPage;$i++):
                        if($i==1):
                            $xml .= '<url>
                            <loc>'.$domain.$lang.'/ksiazki</loc>
                                <lastmod>'.$time.'</lastmod>
                            </url>
                            ';  
                        else:
                            $xml .= '<url>
                            <loc>'.$domain.$lang.'/ksiazki/'.$i.'</loc>
                                <lastmod>'.$time.'</lastmod>
                            </url>
                            ';    
                        endif;
                   endfor;
                   foreach($books as $book):
                        $xml .= '<url>
                        <loc>'.$domain.$lang.'/ksiazki/'.$book['Translation'][$lang]['slug'].'</loc>
                             <lastmod>'.$time.'</lastmod>
                        </url>
                        '; 
                   endforeach;
                   if(count($workshops)%$numberOfWorkshopsPerPage != 0):
                        $workshopPage = (int)(count($workshops)/$numberOfWorkshopsPerPage) + 1;
                   else:
                        $workshopPage = count($workshops)/$numberOfWorkshopsPerPage;
                   endif;
                   for($i=1;$i<=$workshopPage;$i++):
                        if($i==1):
                            $xml .= '<url>
                            <loc>'.$domain.$lang.'/warsztaty</loc>
                                <lastmod>'.$time.'</lastmod>
                            </url>
                            ';  
                        else:
                            $xml .= '<url>
                            <loc>'.$domain.$lang.'/warsztaty/'.$i.'</loc>
                                <lastmod>'.$time.'</lastmod>
                            </url>
                            ';    
                        endif;
                   endfor;
                   foreach($workshops as $workshop):
                        $xml .= '<url>
                        <loc>'.$domain.$lang.'/warsztaty/'.$workshop['Translation'][$lang]['slug'].'</loc>
                             <lastmod>'.$time.'</lastmod>
                        </url>
                        '; 
                   endforeach;
                   if(count($reviews)%$numberOfReviewsPerPage != 0):
                        $reviewPage = (int)(count($reviews)/$numberOfReviewsPerPage) + 1;
                   else:
                        $reviewPage = count($reviews)/$numberOfReviewsPerPage;
                   endif;
                   for($i=1;$i<=$reviewPage;$i++):
                        if($i==1):
                            $xml .= '<url>
                            <loc>'.$domain.$lang.'/w-mediach</loc>
                                <lastmod>'.$time.'</lastmod>
                            </url>
                            ';  
                        else:
                            $xml .= '<url>
                            <loc>'.$domain.$lang.'/w-mediach/'.$i.'</loc>
                                <lastmod>'.$time.'</lastmod>
                            </url>
                            ';    
                        endif;
                   endfor;
                   foreach($reviews as $review):
                        $xml .= '<url>
                        <loc>'.$domain.$lang.'/w-mediach/'.$review['Translation'][$lang]['slug'].'</loc>
                             <lastmod>'.$time.'</lastmod>
                        </url>
                        '; 
                   endforeach;
                   if(count($partners)%$numberOfPartnersPerPage != 0):
                        $partnerPage = (int)(count($partners)/$numberOfPartnersPerPage) + 1;
                   else:
                        $partnerPage = count($partners)/$numberOfPartnersPerPage;
                   endif;
                   for($i=1;$i<=$partnerPage;$i++):
                        if($i==1):
                            $xml .= '<url>
                            <loc>'.$domain.$lang.'/partnerzy</loc>
                                <lastmod>'.$time.'</lastmod>
                            </url>
                            ';  
                        else:
                            $xml .= '<url>
                            <loc>'.$domain.$lang.'/partnerzy/'.$i.'</loc>
                                <lastmod>'.$time.'</lastmod>
                            </url>
                            ';    
                        endif;
                   endfor;
                   foreach($categoriesCooking as $categoryCook):
                        if(count($categoryCook['Recipes'])%$numberOfRecipesPerPage != 0):
                            $categoryCookingPage = (int)(count($categoryCook['Recipes'])/$numberOfRecipesPerPage) + 1;
                        else:
                            $categoryCookingPage = count($categoryCook['Recipes'])/$numberOfRecipesPerPage;
                        endif;
                        for($i=1;$i<=$categoryCookingPage;$i++):
                            if($i==1):
                                $xml .= '<url>
                                <loc>'.$domain.$lang.'/'.$categoryCook['Translation'][$lang]['slug'].'/przepisy</loc>
                                    <lastmod>'.$time.'</lastmod>
                                </url>
                                ';  
                            else:
                                 $xml .= '<url>
                                <loc>'.$domain.$lang.'/'.$categoryCook['Translation'][$lang]['slug'].'/przepisy/'.$i.'</loc>
                                    <lastmod>'.$time.'</lastmod>
                                </url>
                                ';    
                            endif;
                        endfor;
                   endforeach;
                   if(count($recipes)%$numberOfRecipesPerPage != 0):
                        $recipePage = (int)(count($recipes)/$numberOfRecipesPerPage) + 1;
                   else:
                        $recipePage = count($recipes)/$numberOfRecipesPerPage;
                   endif;
                   for($i=1;$i<=$recipePage;$i++):
                        if($i==1):
                            $xml .= '<url>
                            <loc>'.$domain.$lang.'/przepisy</loc>
                                <lastmod>'.$time.'</lastmod>
                            </url>
                            ';  
                        else:
                            $xml .= '<url>
                            <loc>'.$domain.$lang.'/przepisy/'.$i.'</loc>
                                <lastmod>'.$time.'</lastmod>
                            </url>
                            ';    
                        endif;
                   endfor;
                   foreach($recipes as $recipe):
                        $xml .= '<url>
                        <loc>'.$domain.$lang.'/'.$recipe['Categories'][0]['Translation'][$lang]['name'].'/przepis/'.$recipe['Translation'][$lang]['slug'].'</loc>
                             <lastmod>'.$time.'</lastmod>
                        </url>
                        '; 
                   endforeach;
                   $xml .= '<url>
                   <loc>'.$domain.$lang.'/recipe-searching</loc>
                        <lastmod>'.$time.'</lastmod>
                   </url>
                   ';
               endforeach;
                 
        $xml .='</urlset>';
   
        fwrite($file, $xml);
        fclose($file);
    }
}
?>