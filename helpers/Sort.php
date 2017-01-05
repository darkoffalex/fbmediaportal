<?php
namespace app\helpers;

use yii\db\ActiveRecord;

class Sort
{
    /**
     * Finds two item by PK, swaps priority and updates
     * @param $id1
     * @param $id2
     * @param $className
     * @param bool $update
     * @return  array | bool | ActiveRecord[]
     */
    public static function SwapById($id1,$id2,$className,$update = true)
    {
        /* @var $className ActiveRecord */
        /* @var $objItem1 ActiveRecord*/
        /* @var $objItem2 ActiveRecord*/

        $objItem1 = $className::findOne($id1);
        $objItem2 = $className::findOne($id2);

        if($objItem1 != null && $objItem2 != null)
        {
            $p1 = $objItem1->priority;
            $objItem1->priority = $objItem2->priority;
            $objItem2->priority = $p1;

            if($update)
            {
                $objItem1->update();
                $objItem2->update();
            }

            return array($objItem1,$objItem2);
        }

        return false;
    }


    /**
     * Swaps priority of two items
     * @param ActiveRecord $object1
     * @param ActiveRecord $object2
     * @param bool $update
     * @return array | bool | ActiveRecord[]
     */
    public  static function Swap($object1, $object2, $update = true)
    {
        /* @var $object1 ActiveRecord*/
        /* @var $object2 ActiveRecord*/

        //if objects not null
        if($object1 != null && $object2 != null)
        {
            //store first object's priority
            $pr1 = $object1->priority;
            //assign to first object priority pf second
            $object1->priority = $object2->priority;
            //assign to second object stored first object's priority
            $object2->priority = $pr1;

            if($update)
            {
                //update both
                $object1->update();
                $object2->update();
            }

            return array($object1,$object2);
        }

        return false;
    }


    /**
     * Reorders priorities (used for ajax drag-n-drop sequence changing)
     * @param string $className
     * @param array $oldOrder
     * @param array $newOrder
     * @param string $sortOrder
     */
    public static function ReorderItems($className,$oldOrder,$newOrder,$sortOrder = 'priority ASC')
    {
        if(!empty($oldOrder) && !empty($newOrder) && count($oldOrder) == count($newOrder))
        {
            /* @var $className ActiveRecord */
            /* @var $items ActiveRecord[] */
            /* @var $item ActiveRecord */

            //get all items by old order's ID's and sort them by priority
            $items = $className->find()->where(array('id' => $oldOrder))->orderBy($sortOrder)->all();

            if(!empty($items))
            {
                //get max and min priorities
                $minPriority = $items[0]->priority;
                $maxPriority = $items[count($items)-1]->priority;

                //current iteration priority
                $current_priority = $minPriority;

                //foreach ID in new order sequence
                foreach($newOrder as $id)
                {
                    //set current iteration priority
                    $item = $className::findOne($id);
                    $item->priority = $current_priority;
                    $item->update();

                    //increase if not reached max
                    if($current_priority < $maxPriority)
                    {
                        $current_priority++;
                    }
                }
            }

        }
    }

    /**
     * Returns next priority for some item (used in adding)
     * @param $className
     * @param array $condition
     * @param string $field
     * @return int
     */
    public static function GetNextPriority($className,$condition = array(),$field = 'priority')
    {
        /* @var $className ActiveRecord */
        /* @var $itemsAll ActiveRecord[] */

        if(!empty($condition))
        {
            $itemsAll = $className::find()->where($condition)->all();
        }
        else
        {
            $itemsAll = $className::find()->all();
        }

        $max = 0;
        foreach($itemsAll as $item)
        {
            if($item->$field > $max)
            {
                $max = $item->$field;
            }
        }

        return $max + 1;
    }


    /**
     * Moves item's priority higher or lower
     * @param $movingObject ActiveRecord
     * @param string $direction
     * @param string $className
     * @param array $condition
     * @param string $order_by
     */
    public static function Move($movingObject,$direction,$className,$condition = array(),$order_by = 'priority ASC')
    {
        /* @var $className ActiveRecord */
        if(!empty($condition))
        {
            $all = $className::find()->where($condition)->orderBy($order_by)->all();
        }
        else
        {
            $all = $className::find()->orderBy($order_by)->all();
        }

        foreach($all as $index => $obj)
        {
            if($obj == $movingObject)
            {
                if($direction == 'up' && isset($all[$index - 1]))
                {
                    self::Swap($all[$index-1],$obj);
                }

                if($direction == 'down' && isset($all[$index + 1]))
                {
                    self::Swap($all[$index+1],$obj);
                }
            }
        }
    }
}