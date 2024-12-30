<?php
interface IFileIO
{
  function save($data);
  function load();
}

abstract class FileIO implements IFileIO
{
  protected $filepath;

  public function __construct($filename)
  {
    if (!is_readable($filename) || !is_writable($filename)) {
      throw new Exception("Data source $filename is invalid.");
    }
    $this->filepath = realpath($filename);
  }
}

class JsonIO extends FileIO
{
  public function load($assoc = true)
  {
    $file_content = file_get_contents($this->filepath);
    return json_decode($file_content, $assoc) ?: [];
  }

  public function save($data)
  {
    $json_content = json_encode($data, JSON_PRETTY_PRINT);
    file_put_contents($this->filepath, $json_content);
  }
}

class SerializeIO extends FileIO
{
  public function load()
  {
    $file_content = file_get_contents($this->filepath);
    return unserialize($file_content) ?: [];
  }

  public function save($data)
  {
    $serialized_content = serialize($data);
    file_put_contents($this->filepath, $serialized_content);
  }
}

interface IStorage
{
  function add($record): string;
  function findById(string $id);
  function findAll(array $params = []);
  function findOne(array $params = []);
  function update(string $id, $record);
  function delete(string $id);

  function findMany(callable $condition);
  function updateMany(callable $condition, callable $updater);
  function deleteMany(callable $condition);
}

class Storage implements IStorage
{
  protected $contents;
  protected $io;

  public function __construct(IFileIO $io, $assoc = true)
  {
    $this->io = $io;
    $this->contents = (array)$this->io->load($assoc);
  }

  public function __destruct()
  {
    $this->io->save($this->contents);
  }

  public function add($record): string
  {
      $ids = array_column($this->contents, 'id');
      $newId = empty($ids) ? 1 : max($ids) + 1; // Generate a new unique ID
      $record['id'] = $newId; // Assign the new ID
      $this->contents[] = $record; // Append the record
      return (string)$newId;
  }
  



  public function findById(string $id)
  {
      foreach ($this->contents as $item) {
          if ((int)$item['id'] === (int)$id) {
              return $item;
          }
      }
      error_log("Item with ID $id not found.");
      return null;
  }
  



  public function findAll(array $params = [])
{
    return array_filter($this->contents, function ($item) use ($params) {
        foreach ($params as $key => $value) {
            if (!isset($item[$key]) || $item[$key] != $value) { // Loose comparison to handle type differences
                return false;
            }
        }
        return true;
    });
}


  public function findOne(array $params = [])
  {
    $found_items = $this->findAll($params);
    $first_index = array_keys($found_items)[0] ?? NULL;
    return $found_items[$first_index] ?? NULL;
  }

  public function update(string $id, $record)
  {
      foreach ($this->contents as $key => $item) {
          if ((int)$item['id'] === (int)$id) {
              $record['id'] = (int)$id; // Ensure the ID is not changed
              $this->contents[$key] = $record; // Update the record in place
              return; // Exit after updating
          }
      }
      error_log("Item with ID $id not found for update.");
  }
  

public function delete(string $id)
{
    foreach ($this->contents as $key => $item) {
        if ((int)$item['id'] === (int)$id) {
            unset($this->contents[$key]);
            $this->contents = array_values($this->contents); // Reindex to ensure consistency
            return; // Exit after deleting
        }
    }
    error_log("Item with ID $id not found for deletion.");
}



  public function findMany(callable $condition)
  {
    return array_filter($this->contents, $condition);
  }

  public function updateMany(callable $condition, callable $updater)
  {
    array_walk($this->contents, function (&$item) use ($condition, $updater) {
      if ($condition($item)) {
        $updater($item);
      }
    });
  }

  public function save()
  {
    // Use the correct save method to save to file (JSON, Serialize, etc.)
    $this->io->save($this->contents);
  }

  public function deleteMany(callable $condition)
  {
    $this->contents = array_filter($this->contents, function ($item) use ($condition) {
      return !$condition($item);
    });
  }
}
