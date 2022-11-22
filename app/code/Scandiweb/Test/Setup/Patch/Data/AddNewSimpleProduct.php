<?php

declare(strict_types=1);

namespace Scandiweb\Test\Setup\Patch\Data;

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\App\State;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;

class AddNewSimpleProduct implements DataPatchInterface
{
	
	protected ModuleDataSetupInterface $setup;
	
	protected ProductInterfaceFactory $productInterfaceFactory;
	
	protected ProductRepositoryInterface $productRepository;
	
	protected State $appState;
	
	protected EavSetup $eavSetup;
	
	protected StoreManagerInterface $storeManager;
	
	protected SourceItemInterfaceFactory $sourceItemFactory;
	
	protected SourceItemsSaveInterface $sourceItemsSaveInterface;
	
	protected CategoryLinkManagementInterface $categoryLink;
	
	protected array $sourceItems = [];
	
	public function __construct(
		ModuleDataSetupInterface        $setup,
		ProductInterfaceFactory         $productInterfaceFactory,
		ProductRepositoryInterface      $productRepository,
		State                           $appState,
		StoreManagerInterface           $storeManager,
		EavSetup                        $eavSetup,
		SourceItemInterfaceFactory      $sourceItemFactory,
		SourceItemsSaveInterface        $sourceItemsSaveInterface,
		CategoryLinkManagementInterface $categoryLink
	)
	{
		$this->appState = $appState;
		$this->productInterfaceFactory = $productInterfaceFactory;
		$this->productRepository = $productRepository;
		$this->setup = $setup;
		$this->eavSetup = $eavSetup;
		$this->storeManager = $storeManager;
		$this->sourceItemFactory = $sourceItemFactory;
		$this->sourceItemsSaveInterface = $sourceItemsSaveInterface;
		$this->categoryLink = $categoryLink;
	}
	
	/**
	 * @return void
	 */
	public function apply()
	{
		$this->appState->emulateAreaCode('adminhtml', [$this, 'execute']);
	}
	
	public function execute()
	{
		// create the product
		$product = $this->productInterfaceFactory->create();
		
		// check if the product already exists
		if ($product->getIdBySku('EDUPoduct')) {
			return;
		}
		
		$attributeSetId = $this->eavSetup->getAttributeSetId(Product::ENTITY, 'Default');
		
		$product->setTypeId(Type::TYPE_SIMPLE)
			->setAttributeSetId($attributeSetId)
			->setName('EDUPoduct')
			->setSku('EDUPoduct')
			->setUrlKey('eduproduct')
			->setPrice(19.99)
			->setVisibility(Visibility::VISIBILITY_BOTH)
			->setStatus(Status::STATUS_ENABLED);
		
		$websiteIDs = [$this->storeManager->getStore()->getWebsiteId()];
		
		$product->setWebsiteIds($websiteIDs);
		
		// save the product to the repository
		$product = $this->productRepository->save($product);
		
		
		$this->categoryLink->assignProductToCategories($product->getSku(), [16]);
		
		//
	}
	
	/**
	 * @return array|string[]
	 */
	public static function getDependencies()
	{
		return [];
	}
	
	/**
	 * @return array|string[]
	 */
	public function getAliases()
	{
		return [];
	}
}
