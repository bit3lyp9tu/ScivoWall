import unittest
from selenium import webdriver
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.common.by import By


class PythonOrgSearch(unittest.TestCase):

    def setUp(self):
        self.driver = webdriver.Firefox()

    def test_search_in_python_org(self):
        driver = self.driver
        driver.get("http://localhost/scientific_poster_generator/login.php")
        self.assertIn("Poster Generator", driver.title)

        # test login

        filed_name = driver.find_element(By.ID, "name")
        filed_name.send_keys("max5jj")
        filed_name.send_keys(Keys.RETURN)

        filed_pw = driver.find_element(By.ID, "pw")
        filed_pw.send_keys("abc")
        filed_pw.send_keys(Keys.RETURN)

        driver.find_element(By.ID, "login").click()
        self.assertEqual(
            "http://localhost/scientific_poster_generator/login.php",
            driver.current_url,
        )

        driver.find_element(By.ID, "name").clear()
        driver.find_element(By.ID, "pw").clear()

        filed_name = driver.find_element(By.ID, "name")
        filed_name.send_keys("max5")
        filed_name.send_keys(Keys.RETURN)

        filed_pw = driver.find_element(By.ID, "pw")
        filed_pw.send_keys("abc")
        filed_pw.send_keys(Keys.RETURN)

        driver.find_element(By.ID, "login").click()

        self.assertEqual(
            "http://localhost/scientific_poster_generator/projects.php",
            driver.current_url,
        )

        session_id = driver.get_cookie("sessionID")
        self.assertIsNotNone(session_id)

    def tearDown(self):
        self.driver.close()


if __name__ == "__main__":
    unittest.main()
