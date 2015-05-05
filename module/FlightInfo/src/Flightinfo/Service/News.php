<?php

namespace FlightInfo\Service;

use \DateTime;
use \PDOException;
use FlightInfo\Lib\DataSourceAwareInterface;
use \FlightInfo\Service\DatabaseService;

class News implements DataSourceAwareInterface {

    use DatabaseService;

	const NAME = 'news';

	/**
	 * @var \PDO
	 */
	private $pdo;

    /**
     * Get one news entry.
     *
     * @param int $id event ID
     * @return \stdClass
     * @throws Exception
     */
    public function get( $id ){
        try{
            $statement = $this->pdo->prepare("
                SELECT * FROM News WHERE id = :id
            ");
            $statement->execute(array(
                'id' => $id
            ));
            $news = $statement->fetchObject();

            if( !$news ){ return false; }

            $news->created_date = new DateTime($news->created_date);
            $news->modified_date = new DateTime($news->modified_date);

            $groupStatement = $this->pdo->prepare("
                SELECT G.id, G.name, G.name_short, G.url
                FROM `Group` G WHERE id = :id
            ");
            $groupStatement->execute(array(
                'id' => $news->group_id
            ));
            $news->group = $groupStatement->fetchObject();
            return $news;
        }catch (PDOException $e){
            throw new Exception("Can't get news item. news:[{$id}]",0,$e);
        }

    }

	public function fetchAll($page=null, $count=10){
		try{
			if($page !== null){
				$statement = $this->pdo->prepare("
					SELECT * FROM `News` N
					ORDER BY N.created_date DESC
					LIMIT {$page},{$count}
				");
				$statement->execute();
			}else{
				$statement = $this->pdo->prepare("
					SELECT * FROM `News` N
					ORDER BY N.created_date DESC
				");
				$statement->execute();
			}

			$groupStatement = $this->pdo->prepare("
                SELECT G.id, G.name, G.name_short, G.url
                FROM `Group` G WHERE id = :id
            ");

			return array_map(function($i) use ($groupStatement){
				$i->created_date = new DateTime($i->created_date);
				$i->modified_date = new DateTime($i->modified_date);

				$groupStatement->execute(array(
					'id' => $i->group_id
				));
				$i->group = $groupStatement->fetchObject();

				return $i;
			},$statement->fetchAll());
		}catch (PDOException $e){
			throw new Exception("Can't get next news item.",0,$e);
		}
	}

    /**
     * Create news entry.
     *
     * @param array $data
     * @return int ID
     */
    public function create( array $data ){
        try{
            $data['created_date'] = date('Y-m-d H:i:s');
            $data['modified_date'] = date('Y-m-d H:i:s');
            $insertString = $this->insertString('News',$data);
            $statement = $this->pdo->prepare($insertString);
            $statement->execute($data);
            $id = (int)$this->pdo->lastInsertId();
			$data['id'] = $id;
            return $id;
        }catch (PDOException $e){
            throw new Exception("Can't create news entry",0,$e);
        }

    }
    /**
     * Update one entry.
     *
     * @param $id news ID
     * @param array $data
     * @return int row count
     * @throws Exception
	 * @todo created_date
     */
    public function update( $id, array $data ){
        try{
            $data['modified_date'] = date('Y-m-d H:i:s');
			$data['created_date'] = date('Y-m-d H:i:s');
            $updateString = $this->updateString('News',$data, "id={$id}");
            $statement = $this->pdo->prepare($updateString);
            $statement->execute($data);
			$data['id'] = $id;
			$data['created_date'] = new DateTime($data['created_date']);
			$data['modified_date'] = new DateTime($data['modified_date']);
            return $statement->rowCount();
        }catch (PDOException $e){
            throw new Exception("Can't update news entry",0,$e);
        }

    }

    /**
     * Delete one entry.
     *
     * @param $id news ID
     * @return int
     * @throws Exception
     */
    public function delete($id){
		if( ( $news = $this->get( $id ) ) != false ){
			try{
				$statement = $this->pdo->prepare('
                DELETE FROM `News`
                WHERE id = :id');
				$statement->execute(array(
					'id' => $id
				));
				return $statement->rowCount();
			}catch (PDOException $e){

				throw new Exception("can't delete news entry",0,$e);
			}
		}else{
			return 0;
		}

    }

	public function setDataSource(\PDO $pdo){
		$this->pdo = $pdo;
	}
}
