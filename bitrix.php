<?php
//id торгового предложения
$productID = 153;
//количество товара
$productQuantity = 2;
//тип плательщика - физлицо
$personTypeId = 1;
//пользователь
$userId = 1;

if (CModule::IncludeModule('sale')) {
	//создание корзины
	$basket = Bitrix\Sale\Basket::create(SITE_ID);
	//добавление товара
	$item = $basket->createItem("catalog", $productID);
	//добавление количества
	$item->setFields(array(
		'QUANTITY' => $productQuantity,
		'PRODUCT_PROVIDER_CLASS' => 'CCatalogProductProvider',
	));
	//создание заказа на админа
	$order = Bitrix\Sale\Order::create(SITE_ID, $userId);
	//установка типа плательщика
	$order->setPersonTypeId($personTypeId);
	//привязка корзины к заказу
	$order->setBasket($basket);
	//получение коллекции отгрузок
	$shipmentCollection = $order->getShipmentCollection();
	//заказ новый, создается новая коллекция отгрузок
	$shipment = $shipmentCollection->createItem(
		Bitrix\Sale\Delivery\Services\Manager::getObjectById(1)
	);
	//добавление в отгрузку товар из корзины
	$shipmentItemCollection = $shipment->getShipmentItemCollection();
	$item = $shipmentItemCollection->createItem($basket[0]);
	$item->setQuantity($productQuantity);
	//создание оплаты
	$paymentCollection = $order->getPaymentCollection();
	$payment = $paymentCollection->createItem(
		Bitrix\Sale\PaySystem\Manager::getObjectById(1)
	);
	//выставление счета на полную стоимость заказа
	$payment->setField("SUM", $order->getPrice());
	$payment->setField("CURRENCY", $order->getCurrency());
	//сохранение заказа
	$result = $order->save();
	if (!$result->isSuccess()) {
		echo $APPLICATION->GetException();
	}
}
?>