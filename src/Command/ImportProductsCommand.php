<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


use App\Entity\JSONFile;
use App\Entity\Product;

use \JsonMachine\JsonMachine;

use Doctrine\ORM\EntityManagerInterface;

class ImportProductsCommand extends Command
{
    protected static $defaultName = 'import:products';
    protected static $defaultDescription = 'Import products from JSON file';

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function configure(): void
    {
        $this->setDescription(self::$defaultDescription)
             ->addOption('--path', null, InputOption::VALUE_REQUIRED, 'JSON file path');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $path = $input->getOption('path');

        // Check path
        if(!$path || $path === '') {
            $io->error('JSON file path can\'t be null');
            return Command::FAILURE;
        }

        // Get content of file
        $json = \file_get_contents($path);
        $json = \utf8_encode($json);
        
        // Check file content
        if(!$json){

            $io->error('JSON file is empty or its corrupted');
            return Command::FAILURE;

        }

        // Check if json_decode fail
        if(json_last_error() !== JSON_ERROR_NONE){
            $io->error('JSON file decode failed');
            return Command::FAILURE;
        }

        // New register on DB
        $file = new JSONFile();
        $file->setName($path);
        $file->setState('waiting');
        $file->setCreatedAt(new \Datetime());
        $this->em->persist($file);
        $this->em->flush();

        $products = \json_decode($json);

        print_r($products);

        if(!empty($products)){

            $productsChunk = array_chunk($products, 1000);

            foreach($productsChunk as $kL => $list){

                foreach($list as $key => $product){

                    // Currency must be USD
                    if($product->price->currency === 'USD'){
                        $io->error('The product '.$product->styleNumber.' cannot be saved: the currency must be USD');
                        continue;
                    }

                    $p = $this->em->getRepository(Product::class)->findOneBy(['styleNumber' => $product->styleNumber]);

                    // If product not exists, insert into DB
                    if(!$p){

                        if($product->price->currency === 'USD'){
                            $io->error('The product '.$product->styleNumber.' cannot be saved: the currency must be USD');
                            continue;
                        }

                        $this->em->getConnection()->beginTransaction();

                        try {

                            $p = new Product();
                            $p->setStyleNumber($product->styleNumber);
                            $p->setName($product->name);
                            $p->setPriceAmount($product->price->amount);
                            $p->setPriceCurrency($product->price->currency);
                            $p->setImages($product->images);
                            $p->setToSync(true);
                            
                            $this->em->persist($p);
                            $this->em->flush();
                            $this->em->getConnection()->commit();

                        } catch (\Exception $e) {

                            $this->em->getConnection()->rollBack();
                            $io->error('The product '.$product->styleNumber.' cannot be saved: '.$e->getMessage());

                        }
                    
                    // ... updated product
                    } else {

                        $this->em->getConnection()->beginTransaction();

                        try {

                            // Sync is defined to false. If the product has changed sync will set to true
                            $p->setToSync(false);

                            if($p->getStyleNumber() != $product->styleNumber){
                                $p->setStyleNumber($product->styleNumber);
                                $p->setToSync(true);
                            }

                            if($p->getName() != $product->name){
                                $p->setName($product->name);
                                $p->setToSync(true);
                            }

                            if($p->getPriceAmount() != $product->price->amount){
                                $p->setPriceAmount($product->price->amount);
                                $p->setToSync(true);
                            }

                            if($p->getPriceCurrency() != $product->price->currency){
                                $p->setPriceCurrency($product->price->currency);
                                $p->setToSync(true);
                            }

                            if($p->getImages() != $product->images){
                                $p->setImages($product->images);
                                $p->setToSync(true);
                            }

                            $this->em->persist($p);
                            $this->em->flush();
                            $this->em->getConnection()->commit();

                        } catch (\Exception $e) {

                            $this->em->getConnection()->rollBack();
                            $io->text('The product '.$product->styleNumber.' cannot be updated: '.$e->getMessage());
                            
                        }

                    }

                }

                unset($productsChunk[$kL]);

            }
            
        }

        $io->success('JSON import successfully');

        return Command::SUCCESS;

    }
}
